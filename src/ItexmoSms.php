<?php

namespace Agnes\ItexmoSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ItexmoSms
{
    protected $api_code;
    protected $client;
    
    protected $base_url = 'https://api.itexmo.com/api/';

    public function __construct(array $config)
    {
        $this->api_code = $config['api_code'] ?? '';
        $this->client = new Client(['base_url' => $this->base_url]); 
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public function broadcast($numbers, string $message, ?string $sender_id = null): array
    {
        $data =[
            'api_code' => $this->api_code,
            'recipients'=> is_array($reipients) ? json_encode($reipients) : $reipients,
            'message'=> $message,
        ];

        if ($sender_id){
            $data['sender_id'] = $sender_id;
        }
        
        return $this->sendRequest('broadcast', $data);
    }


    // send messages to various recipients . broadcast2d endpoint

    public function broadcast2d(array $messages, ?string $sender_id = null): array
    {
        $data = [
            'api_code'=> $this->api_code,
            'messaages' => json_encode($messages),
        ];

        if ($sender_id){
            $data['sender_id'] = $sender_id;
        }

        return $this->sendRequest('broadcast-d2', $data);

    }


    // send OTP msg to recipient . broadcastOTP endpoint

    public function broadcastOTP(string $recipient, string $message): array
    {
        $data = [
            'api_code' => $this->api_code,
            'recipient' => $recipient,
            'message' => $message,
        ];

        return $this->sendRequest('broadcast-otp', $data);

    }

    // query data from api

    public function query(string $query_type, array $params = []): array
    {
        $data = array_merge([
            'api_code'=> $this->api_code,
            'query_type'=> $query_type,
        ], $params);

        return $this->sendRequest('query', $data);
    }



    private function sendRequest(string $endpoint, array $data): array{
        try{
            $response = $this->client->post($endpoint, ['form_params'=> $data]);
            $body = json_decode($response->getBody(), true);
  
            if (isset($body['status'])) {
                return [
                    'success' => $body['status'] ===0,
                    'message'=> $this->handleApiResponse($body['status']),
                    'data'=>$body
                ];
            }


            return[
                'success'=> false,
                'message'=> 'Invalid API response',
                'data'=> $body,
            ];

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
