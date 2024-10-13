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

    public function testBroadcast()
    {
        $response = $this->itexmo->broadcast(getenv('TEST_PHONE_NUMBER'), 'Test message');
        $this->assertTrue($response['success'], 'The message was not sent successfully: ' . ($response['message'] ?? 'Unknown error'));
    }

    public function testQueryBalance()
    {
        $response = $this->itexmo->query('balance');
        $this->assertTrue($response['success'], 'The query for balance was unsuccessful: ' . ($response['message'] ?? 'Unknown error'));
    }

    public function testBroadcastWithMultipleRecipients()
    {
        $recipients = [getenv('TEST_PHONE_NUMBER1'), getenv('TEST_PHONE_NUMBER2')];
        $response = $this->itexmo->broadcast($recipients, 'Test message to multiple recipients');
        $this->assertTrue($response['success'], 'The messages were not sent successfully: ' . ($response['message'] ?? 'Unknown error'));
    }

    public function testBroadcastWithInvalidNumber()
    {
        $response = $this->itexmo->broadcast('invalid_number', 'Test message');
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('invalid', strtolower($response['message']));
    }

    public function testBroadcast2d()
    {
        $messages = [
            [getenv('TEST_PHONE_NUMBER1'), 'Test message 1'],
            [getenv('TEST_PHONE_NUMBER2'), 'Test message 2'],
        ];
        $response = $this->itexmo->broadcast2d($messages);
        $this->assertTrue($response['success'], 'The 2D broadcast was not successful: ' . ($response['message'] ?? 'Unknown error'));
    }

    public function testBroadcastOTP()
    {
        $response = $this->itexmo->broadcastOTP(getenv('TEST_PHONE_NUMBER'), 'Your OTP is 123456');
        $this->assertTrue($response['success'], 'The OTP message was not sent successfully: ' . ($response['message'] ?? 'Unknown error'));
    }
}