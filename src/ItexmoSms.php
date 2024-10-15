<?php

namespace Itexmo\ItexmoSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ItexmoSms
{
    protected $api_code;
    protected $email;
    protected $password;
    protected $client;
    protected $base_url;
    protected $retry_attempts;
    protected $retry_delay;

    public function __construct(array $config)
    {
        $this->api_code = $config['api_code'] ?? null;
        $this->email = $config['email'] ?? null;
        $this->password = $config['password'] ?? null;

        if (empty($this->api_code) || empty($this->email) || empty($this->password)) {
            throw new \InvalidArgumentException("API code, email, and password are required.");
        }

        $this->base_url = $config['api_base_url'] ?? 'https://api.itexmo.com/api/';
        $this->retry_attempts = $config['retry_attempts'] ?? 3;
        $this->retry_delay = $config['retry_delay'] ?? 5;

        $this->client = new Client(['base_uri' => $this->base_url]);
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
        $contents = array_map(function($message) {
            return [
                'Message' => $message[1],
                'Recipient' => $message[0]
            ];
        }, $messages);

        return $this->sendRequest('broadcast-2d', [
            'Email' => $this->email,
            'Password' => $this->password,
            'ApiCode' => $this->api_code,
            'Contents' => $contents,
        ]);
    }

    private function sendRequest(string $endpoint, array $data): array
    {
        $attempt = 0;
        do {
            try {
                error_log("Sending request to Itexmo API: " . json_encode($data));
                
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
                error_log("Parsed API Response: " . json_encode($body));

                $result = [
                    'success' => !($body['Error'] ?? false),
                    'message' => $body['Message'] ?? 'Unknown response',
                    'data' => $body,
                ];
                error_log("Returning response: " . json_encode($result));

                return $result;
            } catch (RequestException $e) {
                $attempt++;
                $errorMessage = 'HTTP request error: ' . $e->getMessage();
                
                if ($e->hasResponse()) {
                    $responseBody = json_decode($e->getResponse()->getBody(), true);
                    $errorMessage .= ' - ' . ($responseBody['Message'] ?? 'Unknown error');
                }

                error_log("Itexmo API Error: " . $errorMessage);

                if ($attempt >= $this->retry_attempts) {
                    return [
                        'success' => false,
                        'message' => $errorMessage,
                        'data' => null,
                    ];
                }

                sleep($this->retry_delay);
            }
        } while ($attempt < $this->retry_attempts);
    }
}