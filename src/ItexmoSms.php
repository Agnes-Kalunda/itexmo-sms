<?php

namespace Itexmo\ItexmoSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ItexmoSms
{
    protected $api_code;
    protected $client;
    protected $base_url;
    protected $default_sender_id;
    protected $maxMessageLength;
    protected $retry_attempts;
    protected $retry_delay;

    public function __construct(array $config)
    {
        $this->api_code = $config['api_code'] ?? '';
        $this->base_url = $config['api_base_url'] ?? 'https://api.itexmo.com/api/';
        $this->default_sender_id = $config['default_sender_id'] ?? '';
        $this->maxMessageLength = $config['max_message_length'] ?? 160;
        $this->retry_attempts = $config['retry_attempts'] ?? 3;
        $this->retry_delay = $config['retry_delay'] ?? 5;

        $this->client = new Client(['base_uri' => $this->base_url]);
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function broadcast($recipients, string $message, ?string $sender_id = null): array
    {
        $this->validateMessageLength($message);
        $sender_id = $sender_id ?? $this->default_sender_id;

        $data = [
            'api_code' => $this->api_code,
            'recipients' => is_array($recipients) ? json_encode($recipients) : $recipients,
            'message' => $message,
            'sender_id' => $sender_id,
        ];

        return $this->sendRequest('broadcast', $data);
    }

    public function broadcast2d(array $messages, ?string $sender_id = null): array
    {
        foreach ($messages as $msg) {
            $this->validateMessageLength($msg[1]);
        }

        $sender_id = $sender_id ?? $this->default_sender_id;

        $data = [
            'api_code' => $this->api_code,
            'messages' => json_encode($messages),
            'sender_id' => $sender_id,
        ];

        return $this->sendRequest('broadcast-d2', $data);
    }

    public function broadcastOTP(string $recipient, string $message): array
    {
        $this->validateMessageLength($message);

        $data = [
            'api_code' => $this->api_code,
            'recipient' => $recipient,
            'message' => $message,
        ];

        return $this->sendRequest('broadcast-otp', $data);
    }

    public function query(string $query_type, array $params = []): array
    {
        $data = array_merge([
            'api_code'=> $this->api_code,
            'query_type'=> $query_type,
        ], $params);

        return $this->sendRequest('query', $data);
    }

    private function sendRequest(string $endpoint, array $data): array {
        try {
            $response = $this->client->post($endpoint, ['form_params' => $data]);
            $body = json_decode($response->getBody(), true);

            if (isset($body['status'])) {
                return [
                    'success' => $body['status'] === 0,
                    'message' => $this->handleApiResponse($body['status']),
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
                'message' => $e->hasResponse() && $e->getResponse()->getStatusCode() === 401
                    ? 'Unauthorized request. Check your API Key.'
                    : 'HTTP request error: ' . $e->getMessage(),
                "data" => null,
            ];
        }
    }

    private function validateMessageLength(string $message): void {
        if (strlen($message) > $this->maxMessageLength) {
            throw new \InvalidArgumentException("Message exceeds maximum length of {$this->maxMessageLength} characters.");
        }
    }

    private function handleApiResponse(int $status): string {
        $responses = [
            0 => 'Message sent successfully.',
            1 => 'Invalid Number.',
            2 => 'No balance or insufficient credit.',
            3 => 'Invalid API code.',
            4 => 'Maximum number of characters exceeded.',
            5 => 'SMS is blocked due to spam content.',
            6 => 'Invalid sender name.',
            7 => 'Invalid mobile number format.',
            8 => 'Unauthorized request or API not allowed.',
            9 => 'API deactivated.',
        ];

        return $responses[$status] ?? 'Unrecognized status code.';
    }
}
