<?php 

use Agnes\ItexmoSms\ItexmoSms;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;

class ItexmoSmsTest extends TestCase{
    protected $itexmoSms;
    protected $mockClient;

    protected function setUp(){

        // mock GuzzleHttp client
        $this->mockClient = Mockery::mock(Client::class);

        // mock config
        $config =[
            "email"=> "email@example.com",
            "password"=> "password",
            "api_code"=> "api_code",
        ];
    }
}

?>