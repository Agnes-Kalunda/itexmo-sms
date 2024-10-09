<?php

namespace Agnes\ItexmoSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ItexmoSms
{
    protected $api_code;
    protected $client;

    public function __construct(array $config)
    {
        $this->api_code = $config['api_code'] ?? '';
        $this->client = new Client(); 
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function broadcast($numbers, $message)
    {
        try {
            $response = $this->client->post('/api/send', [
                'json' => [
                    'api_code' => $this->api_code,
                    'numbers' => $numbers,
                    'message' => $message,
                ]
            ]);

            $body = json_decode($response->getBody(), true);

            // handle response based on Itexmo documentatios
            if (isset($body['status'])) {
                return $this->handleApiResponse($body['status']);
            }

            throw new \RuntimeException('Invalid API response');

        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                if ($statusCode === 401) {
                    return 'Unauthorized request. Check your API key.';
                }
            }

            
            throw new \RuntimeException('HTTP request error: ' . $e->getMessage());
        }
    }

    /**
     * handle different API status codes according to Itexmo documentation.
     */
    private function handleApiResponse($status)
    {
        switch ($status) {
            case 0:
                return 'SUCCESS: Message sent successfully.';
            case 1:
                return 'ERROR: Invalid Number.';
            case 2:
                return 'ERROR: No balance or insufficient credit.';
            case 3:
                return 'ERROR: Invalid API code.';
            case 4:
                return 'ERROR: Maximum number of characters exceeded.';
            case 5:
                return 'ERROR: SMS is blocked due to spam content.';
            case 6:
                return 'ERROR: Invalid sender name.';
            case 7:
                return 'ERROR: Invalid mobile number format.';
            case 8:
                return 'ERROR: Unauthorized request or API not allowed.';
            case 9:
                return 'ERROR: API deactivated.';
            default:
                return 'ERROR: Unrecognized status code.';
        }
    }
}
