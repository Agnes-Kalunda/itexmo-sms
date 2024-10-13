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

    public function broadcast($recipients, string $message, ?string $sender_id = null): array
    {
        $data = [
            'email' => $this->email,
            'password' => $this->password,
            'ApiCode' => $this->api_code,
            'Recipients' => is_array($recipients) ? json_encode($recipients) : $recipients,
            'Message' => $message,
        ];

        if ($sender_id) {
            $data['SenderId'] = $sender_id;
        }

        return $this->sendRequest('broadcast', $data);
    }

    public function broadcast2d(array $messages, ?string $sender_id = null): array
    {
        $data = [
            'email' => $this->email,
            'password' => $this->password,
            'ApiCode' => $this->api_code,
            'Messages' => json_encode($messages),
        ];

        if ($sender_id) {
            $data['SenderId'] = $sender_id;
        }

        return $this->sendRequest('broadcast-2d', $data);
    }

    public function broadcastOTP(string $recipient, string $message): array
    {
        $data = [
            'email' => $this->email,
            'password' => $this->password,
            'ApiCode' => $this->api_code,
            'Recipients' => $recipient,
            'Message' => $message,
        ];

        return $this->sendRequest('broadcast-otp', $data);
    }

    public function query(string $query_type, array $params = []): array
    {
        $data = array_merge([
            'email' => $this->email,
            'password' => $this->password,
            'ApiCode' => $this->api_code,
            'QueryType' => $query_type,
        ], $params);

        return $this->sendRequest('query', $data);
    }

    private function sendRequest(string $endpoint, array $data): array
    {
        $attempt = 0;
        do {
            try {
                error_log("Sending request to Itexmo API: " . json_encode($data));
                
                $response = $this->client->post($endpoint, [
                    'form_params' => $data,
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                ]);
                
                $body = json_decode($response->getBody(), true);
                error_log("Itexmo API Response: " . json_encode($body));

                return [
                    'success' => !($body['Error'] ?? true),
                    'message' => $body['Message'] ?? 'Unknown response',
                    'data' => $body,
                ];
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