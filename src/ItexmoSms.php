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
            'recipients' => is_array($recipients) ? json_encode($recipients) : $recipients,
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
            return [
                'success' => false,
                'message' => $e->hasResponse() && $e->getResponse()->getStatusCode() === 401
                    ? 'Unauthorized request. Check your API Key.'
                    : 'HTTP request error:' .$e->getMessage(),
                "data" => null,
            ];
        }
    }
          

    /**
     * handle different API status codes according to Itexmo documentation.
     */
    private function handleApiResponse(int $status): string{

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

        return $responses[$status] ??'Unrecognized status code.';
    }
}
