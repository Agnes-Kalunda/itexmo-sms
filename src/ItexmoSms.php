<?php

namespace Agnes\ItexmoSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ItexmoSms {
    protected $client;
    protected $email;
    protected $password;
    protected $api_code;

    public function __construct(array $config, Client $client = null) {
        $this->client = $client ?? new Client(); 
        $this->email = $config['email'];
        $this->password = $config['password'];
        $this->api_code = $config['api_code'];
    }

    public function broadcast(array $recipients, string $message) {
        $payload = [
            'form_params' => [
                'Recipients' => implode(',', $recipients),
                'Message' => $message,
                'Email' => $this->email,
                'Password' => $this->password,
                'ApiCode' => $this->api_code,
            ],
        ];

        try {
            $response = $this->client->post('broadcast', $payload);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Handle exceptions
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function broadcast2d(array $messages) {
        $payload = [
            'form_params' => [
                'Messages' => json_encode($messages),
                'Email' => $this->email,
                'Password' => $this->password,
                'ApiCode' => $this->api_code,
            ],
        ];

        try {
            $response = $this->client->post('broadcast-2d', $payload);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function broadcastOtp(string $recipient, string $message) {
        $payload = [
            'form_params' => [
                'Recipients' => $recipient,
                'Message' => $message,
                'Email' => $this->email,
                'Password' => $this->password,
                'ApiCode' => $this->api_code,
            ],
        ];

        try {
            $response = $this->client->post('broadcast-otp', $payload);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function query(array $params) {
        $payload = [
            'form_params' => array_merge($params, [
                'Email' => $this->email,
                'Password' => $this->password,
                'ApiCode' => $this->api_code,
            ]),
        ];

        try {
            $response = $this->client->post('query', $payload);
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
        
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
