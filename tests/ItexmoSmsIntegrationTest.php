<?php

namespace Itexmo\ItexmoSms\Tests;

use Itexmo\ItexmoSms\ItexmoSms;
use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;

class ItexmoSmsIntegrationTest extends TestCase
{
    protected $itexmo;

    protected function setUp(): void
    {
        $dotenv = Dotenv::create(__DIR__ . '/..');
        $dotenv->load();

        $this->itexmo = new ItexmoSms([
            'api_code' => getenv('ITEXMO_API_CODE'),
            'email' => getenv('ITEXMO_EMAIL'),
            'password' => getenv('ITEXMO_PASSWORD'),
        ]);

        $this->assertNotNull($this->itexmo, 'ItexmoSms failed to initialize');

        error_log("API Code: " . getenv('ITEXMO_API_CODE'));
        error_log("Email: " . getenv('ITEXMO_EMAIL'));
        error_log("Password: " . getenv('ITEXMO_PASSWORD'));
    }

    public function testCheckBalance()
    {
        $response = $this->itexmo->checkBalance();
        error_log("Test Check Balance Response: " . json_encode($response));
        
        $this->assertArrayHasKey('success', $response, 'Response does not contain "success" key');
        $this->assertArrayHasKey('message', $response, 'Response does not contain "message" key');
        $this->assertArrayHasKey('data', $response, 'Response does not contain "data" key');
        
        $this->assertTrue($response['success'], 'The balance check was unsuccessful: ' . ($response['message'] ?? 'Unknown error'));
        
        if (isset($response['data'])) {
            $this->assertArrayHasKey('MessagesLeft', $response['data'], 'Response does not contain "MessagesLeft" key');
        }
    }

    public function testBroadcast()
    {
        $recipients = [getenv('TEST_PHONE_NUMBER1'), getenv('TEST_PHONE_NUMBER')];
        $response = $this->itexmo->broadcast($recipients, 'Test broadcast message');
        error_log("Test Broadcast Response: " . json_encode($response));
        
        $this->assertArrayHasKey('success', $response, 'Response does not contain "success" key');
        $this->assertArrayHasKey('message', $response, 'Response does not contain "message" key');
        $this->assertArrayHasKey('data', $response, 'Response does not contain "data" key');
        
        $this->assertTrue($response['success'], 'The broadcast was not successful: ' . ($response['message'] ?? 'Unknown error'));
        
        if (isset($response['data'])) {
            $this->assertFalse($response['data']['Error'] ?? true, 'API reported an error');
            $this->assertEquals(0, $response['data']['Failed'] ?? -1, 'Some messages failed to send');
            $this->assertEquals(2, $response['data']['TotalSMS'] ?? 0, 'Incorrect number of SMS sent');
        }
    }

    public function testBroadcastOTP()
    {
        $recipients = [getenv('TEST_PHONE_NUMBER')];
        $response = $this->itexmo->broadcastOTP($recipients, 'Your OTP is 123456');
        error_log("Test Broadcast OTP Response: " . json_encode($response));
        
        $this->assertArrayHasKey('success', $response, 'Response does not contain "success" key');
        $this->assertArrayHasKey('message', $response, 'Response does not contain "message" key');
        $this->assertArrayHasKey('data', $response, 'Response does not contain "data" key');
        
        $this->assertTrue($response['success'], 'The OTP broadcast was not successful: ' . ($response['message'] ?? 'Unknown error'));
        
        if (isset($response['data'])) {
            $this->assertFalse($response['data']['Error'] ?? true, 'API reported an error');
            $this->assertEquals(0, $response['data']['Failed'] ?? -1, 'Some messages failed to send');
        }
    }

    public function testBroadcast2d()
    {
        $messages = [
            [getenv('TEST_PHONE_NUMBER1'), 'Test message 1'],
            [getenv('TEST_PHONE_NUMBER'), 'Test message 2'],
        ];
        $response = $this->itexmo->broadcast2d($messages);
        error_log("Test Broadcast 2D Response: " . json_encode($response));
        
        $this->assertArrayHasKey('success', $response, 'Response does not contain "success" key');
        $this->assertArrayHasKey('message', $response, 'Response does not contain "message" key');
        $this->assertArrayHasKey('data', $response, 'Response does not contain "data" key');
        
        $this->assertTrue($response['success'], 'The 2D broadcast was not successful: ' . ($response['message'] ?? 'Unknown error'));
        
        if (isset($response['data'])) {
            $this->assertFalse($response['data']['Error'] ?? true, 'API reported an error');
            $this->assertEquals(0, $response['data']['Failed'] ?? -1, 'Some messages failed to send');
            $this->assertEquals(2, $response['data']['TotalSMS'] ?? 0, 'Incorrect number of SMS sent');
        }
    }
}