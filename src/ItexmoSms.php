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
    
}



?>