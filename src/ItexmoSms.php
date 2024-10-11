<?php

namespace Itexmo\ItexmoSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ItexmoSms
{
    protected $email;
    protected $password;
    protected $apiCode;
    protected $client;
    protected $baseUrl;
    protected $defaultSenderId;
    protected $maxMessageLength;
    protected $retryAttempts;
    protected $retryDelay;

    public function __construct(array $config)
    {
        $this->email = $config['email'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->apiCode = $config['api_code'] ?? '';
        $this->baseUrl = $config['api_base_url'] ?? 'https://api.itexmo.com/api/';
        $this->defaultSenderId = $config['default_sender_id'] ?? '';
        $this->maxMessageLength = $config['max_message_length'] ?? 160;
        $this->retryAttempts = $config['retry_attempts'] ?? 3;
        $this->retryDelay = $config['retry_delay'] ?? 5;

        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function broadcast(array $recipients, string $message, ?string $senderId = null): array
    {
        $this->validateMessageLength($message);
        $senderId = $senderId ?? $this->defaultSenderId;

        $data = [
            'Email' => $this->email,
            'Password' => $this->password,
            'ApiCode' => $this->apiCode,
            'Recipients' => $recipients,
            'Message' => $message,
            'SenderId' => $senderId,
        ];

        return $this->sendRequest('broadcast', $data);
    }

    public function broadcast2d(array $contents, ?string $senderId = null): array
    {
        foreach ($contents as $content) {
            $this->validateMessageLength($content['Message']);
        }

        $senderId = $senderId ?? $this->defaultSenderId;

        $data = [
            'Email' => $this->email,
            'Password' => $this->password,
            'ApiCode' => $this->apiCode,
            'Contents' => $contents,
            'SenderId' => $senderId,
        ];

        return $this->sendRequest('broadcast-2d', $data);
    }

    public function broadcastOTP(string $recipient, string $message): array
    {
        $this->validateMessageLength($message, 160);

        $data = [
            'Email' => $this->email,
            'Password' => $this->password,
            'ApiCode' => $this->apiCode,
            'Recipients' => [$recipient],
            'Message' => $message,
        ];

        return $this->sendRequest('broadcast-otp', $data);
    }

    public function query(string $action, array $params = []): array
    {
        $data = array_merge([
            'Email' => $this->email,
            'Password' => $this->password,
            'ApiCode' => $this->apiCode,
            'Action' => $action,
        ], $params);

        return $this->sendRequest('query', $data);
    }

    private function sendRequest(string $endpoint, array $data): array
    {
        try {
            $response = $this->client->post($endpoint, [
                'json' => $data,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);
            $body = json_decode($response->getBody(), true);

            if (isset($body['Error'])) {
                return [
                    'success' => !$body['Error'],
                    'message' => $body['Message'] ?? 'Unknown error occurred.',
                    'data' => $body
                ];
            }

            return [
                'success' => false,
                'message' => 'Invalid API response',
                'data' => $body,
            ];

        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => $e->hasResponse() && $e->getResponse()->getStatusCode() === 400
                    ? 'Bad request. Check your parameters.'
                    : 'HTTP request error: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    private function validateMessageLength(string $message, int $maxLength = null): void
    {
        $maxLength = $maxLength ?? $this->maxMessageLength;
        if (strlen($message) > $maxLength) {
            throw new \InvalidArgumentException("Message exceeds maximum length of {$maxLength} characters.");
        }
    }
}