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

        // instance of ItexmoSms with mocked client

        $this->itexmoSms = new ItexmoSms($config);
        $this->itexmoSms->client = $this->mockClient;
    }

    public function testBroadcast(){
        // mock response from HTTP client
        $this->mockClient
            ->shouldReceive("post")
            ->once()
            ->with("broadcast", Mockery::on(function($payload){
                return isset($payload['form_params']['Recipients']);

            }))
            ->andReturn(new Response(200,[], json_decode(['status' => 'ok'])));

    
    
            

    }
}

?>