<?php

namespace Agnes\ItexmoSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;

class ItexmoSms
{
    protected $client;
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => 'https://itexmo.com/', // base URI
            'timeout'  => 5.0,  
        ]);
    }

 
    public function broadcast(array $recipients, string $message)
    {
        try {
            $response = $this->client->post('broadcast', [
                'form_params' => [
                    'Recipients' => $recipients,
                    'Message'    => $message,
                    'ApiCode'    => $this->config['api_code'],
                ],
            ]);

            return $this->handleResponse($response);
        } catch (RequestException $e) {
            return $this->handleRequestError($e);
        } catch (ConnectException $e) {
            return ['error' => 'Network error: Unable to connect to the API.'];
        } catch (\Exception $e) {
            return ['error' => 'An unexpected error occurred: ' . $e->getMessage()];
        }
    }

   
    public function broadcast2d(array $messages)
    {
        try {
            $response = $this->client->post('broadcast-2d', [
                'form_params' => [
                    'Messages' => $messages,
                    'ApiCode'  => $this->config['api_code'],
                ],
            ]);

            return $this->handleResponse($response);
        } catch (RequestException $e) {
            return $this->handleRequestError($e);
        } catch (ConnectException $e) {
            return ['error' => 'Network error: Unable to connect to the API.'];
        } catch (\Exception $e) {
            return ['error' => 'An unexpected error occurred: ' . $e->getMessage()];
        }
    }

    
    public function broadcastOtp(string $recipient, string $message)
    {
        try {
            $response = $this->client->post('broadcast-otp', [
                'form_params' => [
                    'Recipient' => $recipient,
                    'Message'   => $message,
                    'ApiCode'   => $this->config['api_code'],
                ],
            ]);

            return $this->handleResponse($response);
        } catch (RequestException $e) {
            return $this->handleRequestError($e);
        } catch (ConnectException $e) {
            return ['error' => 'Network error: Unable to connect to the API.'];
        } catch (\Exception $e) {
            return ['error' => 'An unexpected error occurred: ' . $e->getMessage()];
        }
    }

    
    //  Query the API
    
    public function query(array $params)
    {
        try {
            $response = $this->client->post('query', [
                'form_params' => $params,
            ]);

            return $this->handleResponse($response);
        } catch (RequestException $e) {
            return $this->handleRequestError($e);
        } catch (ConnectException $e) {
            return ['error' => 'Network error: Unable to connect to the API.'];
        } catch (\Exception $e) {
            return ['error' => 'An unexpected error occurred: ' . $e->getMessage()];
        }
    }

    
    // handle API response
    
    private function handleResponse($response)
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode == 200) {
            return json_decode($response->getBody(), true);
        }

        return [
            'error' => 'Unexpected status code: ' . $statusCode
        ];
    }

    
    //  handle request errors from Guzzle
    
    private function handleRequestError(RequestException $e)
    {
        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode == 401) {
                return ['error' => 'Unauthorized: Invalid API key or credentials.'];
            }

            return [
                'error' => 'API request failed with status ' . $statusCode,
                'details' => json_decode($response->getBody(), true)
            ];
        }

        return ['error' => 'Request error: ' . $e->getMessage()];
    }
}
