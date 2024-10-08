<?php

namespace Agnes\ItexmoSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class ItexmoSms
{
    protected $client;
    protected $apiCode;

    public function __construct(array $config)
    {
        $this->apiCode = $config['api_code'];
        $this->client = new Client(['base_uri' => 'https://api.itexmo.com/']);
    }

    // Setter method for client to allow injection during testing
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function broadcast(array $recipients, string $message)
    {
        try {
            $response = $this->client->post('/api/send', [
                'json' => [
                    'to' => $recipients,
                    'message' => $message,
                    'api_code' => $this->apiCode
                ]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return null; // Handle unexpected status codes
            }

            // Attempt to decode the response
            $responseBody = (string) $response->getBody();
            $responseData = json_decode($responseBody, true);

            // Return null if JSON is invalid
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return $responseData;
        } catch (ConnectException $e) {
            // Handle network connection errors (e.g., timeout)
            throw new \RuntimeException('Network connection error: ' . $e->getMessage());
        } catch (RequestException $e) {
            // Handle other request errors
            throw new \RuntimeException('HTTP request error: ' . $e->getMessage());
        } catch (\Exception $e) {
            // General error handling
            throw new \RuntimeException('An error occurred: ' . $e->getMessage());
        }
    }
}
