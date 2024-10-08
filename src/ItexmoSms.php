<?php 
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ItexmoSms{
    protected $config;
    protected $client;


    public function __construct(array $config){
        $this->config = $config;
        $this->client = new Client([
            'base_uri'=> 'https://api.itexmo.com/api/',
            'timeout'=>10.0,
        ]);

    }

    public function broadcast(array $recipients, string $message, ?string $senderId =null){
        $payload = [
            'Email'=> $this->config['email'],
            'password' => $this->config['password'],
            'ApiCode' => $this->config['api_code'],
            'Recipients' => json_encode($recipients),
            'Message' => $message,
        ];

        if ($senderId){
            $payload['SenderId'] = $senderId;
        }

        return $this->sendRequest('broadcast', $payload);
    }

    // funto handle broadcast-2d endpoint

    public function broadcast2d(array $messages, ?string $senderId = null){
        $payload = [
            'Email' => $this->config['email'],
            'password' => $this->config['password'],
            'ApiCode' => $this->config['api_code'],
            'Messages' => json_encode($messages),
        ];

        if ($senderId){
            $payload['SenderId'] = $senderId;
        }

        return $this->sendRequest('broadcast-2d', $payload);


    }


    public function broadcastOtp(string $recipient, string $message){
        $payload = [
            'Email' => $this->config['email'],
            'password' => $this->config['password'],
            'ApiCode' => $this->config['api_code'],
            'Recipients' => json_encode([$recipient]),
            'Message' => $message,
        ];

        return $this->sendRequest('boradcast-otp', $payload);
    }
    

}



?>