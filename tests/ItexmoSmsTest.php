<?php

use Agnes\ItexmoSms\ItexmoSms;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;

class ItexmoSmsTest extends TestCase {
    protected $itexmoSms;
    protected $mockClient;

    protected function setUp(): void {
        // Mock the GuzzleHttp client
        $this->mockClient = Mockery::mock(Client::class);

        // Mock the config
        $config = [
            'email' => 'your_email@example.com',
            'password' => 'your_password',
            'api_code' => 'your_api_code',
        ];

        // Create an instance of ItexmoSms with mocked client
        $this->itexmoSms = new ItexmoSms($config, $this->mockClient);
    }

    public function testBroadcast() {
        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('broadcast', Mockery::on(function ($payload) {
                return isset($payload['form_params']['Recipients']);
            }))
            ->andReturn(new Response(200, [], json_encode(['status' => 'OK'])));

        $response = $this->itexmoSms->broadcast(['12345678909'], 'Test message');

        // Assert response
        $this->assertEquals(['status' => 'OK'], $response);
    }

    public function testBroadcast2d() {
        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('broadcast-2d', Mockery::on(function ($payload) {
                return isset($payload['form_params']['Messages']);
            }))
            ->andReturn(new Response(200, [], json_encode(['status' => 'OK'])));

        $messages = [
            ['Recipient' => '12345678909', 'Message' => 'Message 1'],
            ['Recipient' => '98765432109', 'Message' => 'Message 2'],
        ];

        $response = $this->itexmoSms->broadcast2d($messages);

        $this->assertEquals(['status' => 'OK'], $response);
    }

    public function testBroadcastOtp() {
        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('broadcast-otp', Mockery::on(function ($payload) {
                return isset($payload['form_params']['Recipients']);
            }))
            ->andReturn(new Response(200, [], json_encode(['status' => 'OK'])));

        $response = $this->itexmoSms->broadcastOtp('12345678909', 'Your OTP is 123456');

        $this->assertEquals(['status' => 'OK'], $response);
    }

    public function testQuery() {
        $this->mockClient
            ->shouldReceive('post')
            ->once()
            ->with('query', Mockery::on(function ($payload) {
                return isset($payload['form_params']['ApiCode']);
            }))
            ->andReturn(new Response(200, [], json_encode(['status' => 'OK'])));

        $response = $this->itexmoSms->query(['ApiCode' => 'api_key']);

        // Assert response
        $this->assertEquals(['status' => 'OK'], $response);
    }

    protected function tearDown(): void {
        Mockery::close();
    }
}
