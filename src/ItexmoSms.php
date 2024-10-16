<?php

namespace Itexmo\ItexmoSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use InvalidArgumentException;

class ItexmoSmsException extends \Exception {}
class ValidationException extends ItexmoSmsException {}
class ApiException extends ItexmoSmsException {}
class NetworkException extends ItexmoSmsException {}

class ItexmoSms
{
    protected $api_code;
    protected $email;
    protected $password;
    protected $client;
    protected $base_url;
    protected $retry_attempts;
    protected $retry_delay;
    
    // API response codes
    const ERROR_CODES = [
        '0' => 'Success',
        '-1' => 'Invalid API Code',
        '-2' => 'Invalid Email or Password',
        '-3' => 'Insufficient Credits',
        '-4' => 'Invalid Phone Number',
        '-5' => 'Invalid Message',
        '-6' => 'System Error',
        '-7' => 'Empty Required Parameters',
        '-8' => 'API Limit Reached',
    ];

    public function __construct(array $config)
    {
        $this->validateConfig($config);
        
        $this->api_code = $config['api_code'];
        $this->email = $config['email'];
        $this->password = $config['password'];
        $this->base_url = $config['api_base_url'] ?? 'https://api.itexmo.com/api/';
        $this->retry_attempts = $config['retry_attempts'] ?? 3;
        $this->retry_delay = $config['retry_delay'] ?? 5;

        $this->client = new Client([
            'base_uri' => $this->base_url,
            'timeout' => $config['timeout'] ?? 30,
            'connect_timeout' => $config['connect_timeout'] ?? 10
        ]);
    }

    protected function validateConfig(array $config): void
    {
        if (empty($config['api_code'])) {
            throw new ValidationException('API code is required');
        }
        if (empty($config['email'])) {
            throw new ValidationException('Email is required');
        }
        if (empty($config['password'])) {
            throw new ValidationException('Password is required');
        }
        if (!filter_var($config['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }
    }

    protected function validatePhoneNumbers(array $recipients): void
    {
        if (empty($recipients)) {
            throw new ValidationException('Recipients list cannot be empty');
        }

        foreach ($recipients as $recipient) {
            if (!preg_match('/^(\+63|0)[0-9]{10}$/', $recipient)) {
                throw new ValidationException("Invalid phone number format: {$recipient}. Must be in +63 or 0 format with 10 digits");
            }
        }
    }

    protected function validateMessage(?string $message): void
    {
        if (empty($message)) {
            throw new ValidationException('Message cannot be empty');
        }

        if (mb_strlen($message) > 160) {
            throw new ValidationException('Message length exceeds 160 characters');
        }
    }

    public function checkBalance(): array
    {
        return $this->sendRequest('query', [
            'Email' => $this->email,
            'Password' => $this->password,
            'ApiCode' => $this->api_code,
            'Action' => 'ApiCodeInfo'
        ]);
    }

    public function broadcast(array $recipients, string $message): array
    {
        $this->validatePhoneNumbers($recipients);
        $this->validateMessage($message);

        return $this->sendRequest('broadcast', [
            'Email' => $this->email,
            'Password' => $this->password,
            'ApiCode' => $this->api_code,
            'Recipients' => $recipients,
            'Message' => $message,
        ]);
    }

    public function broadcastOTP(array $recipients, string $message): array
    {
        $this->validatePhoneNumbers($recipients);
        $this->validateMessage($message);

        return $this->sendRequest('broadcast-otp', [
            'Email' => $this->email,
            'Password' => $this->password,
            'ApiCode' => $this->api_code,
            'Recipients' => $recipients,
            'Message' => $message,
        ]);
    }

    public function broadcast2d(array $messages): array
    {
        if (empty($messages)) {
            throw new ValidationException('Messages array cannot be empty');
        }

        $contents = [];
        foreach ($messages as $index => $message) {
            if (!is_array($message) || count($message) !== 2) {
                throw new ValidationException("Invalid message format at index {$index}. Expected [phone_number, message]");
            }

            $this->validatePhoneNumbers([$message[0]]);
            $this->validateMessage($message[1]);

            $contents[] = [
                'Message' => $message[1],
                'Recipient' => $message[0]
            ];
        }

        return $this->sendRequest('broadcast-2d', [
            'Email' => $this->email,
            'Password' => $this->password,
            'ApiCode' => $this->api_code,
            'Contents' => $contents,
        ]);
    }

    protected function handleApiError(array $response): void
    {
        if (isset($response['Status']) && $response['Status'] !== '0') {
            $errorMessage = self::ERROR_CODES[$response['Status']] ?? 'Unknown API error';
            throw new ApiException("API Error: {$errorMessage}", (int)$response['Status']);
        }

        if ($response['Error'] ?? false) {
            throw new ApiException('API Error: ' . ($response['Message'] ?? 'Unknown error'));
        }
    }

    private function sendRequest(string $endpoint, array $data): array
    {
        $attempt = 0;
        $lastException = null;

        do {
            try {
                error_log("Attempt {$attempt}: Sending request to Itexmo API: " . json_encode($data));
                
                $response = $this->client->post($endpoint, [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]);
                
                $rawBody = (string) $response->getBody();
                error_log("Raw API Response: " . $rawBody);
                
                $body = json_decode($rawBody, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new ApiException('Invalid JSON response from API: ' . json_last_error_msg());
                }

                error_log("Parsed API Response: " . json_encode($body));

                // check for API-level errors
                $this->handleApiError($body);

                $result = [
                    'success' => true,
                    'message' => $body['Message'] ?? 'Success',
                    'data' => $body,
                ];
                error_log("Returning response: " . json_encode($result));

                return $result;

            } catch (ConnectException $e) {
                $lastException = new NetworkException(
                    'Failed to connect to Itexmo API: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            } catch (RequestException $e) {
                $errorMessage = 'HTTP request error: ' . $e->getMessage();
                
                if ($e->hasResponse()) {
                    try {
                        $responseBody = json_decode($e->getResponse()->getBody(), true);
                        $errorMessage .= ' - ' . ($responseBody['Message'] ?? 'Unknown error');
                    } catch (\Exception $jsonException) {
                        $errorMessage .= ' (Failed to parse error response)';
                    }
                }

                $lastException = new ApiException($errorMessage, $e->getCode(), $e);
                error_log("Itexmo API Error: " . $errorMessage);
            }

            $attempt++;
            if ($attempt < $this->retry_attempts) {
                sleep($this->retry_delay);
            }

        } while ($attempt < $this->retry_attempts);

        // If we've exhausted all retry attempts, throw the last exception
        throw $lastException ?? new ApiException('Maximum retry attempts reached with no successful response');
    }
}