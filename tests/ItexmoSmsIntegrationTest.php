<?php

namespace Itexmo\ItexmoSms\Tests;

use Itexmo\ItexmoSms\ItexmoSms;
use PHPUnit\Framework\TestCase;

class ItexmoSmsIntegrationTest extends TestCase
{
    protected $itexmo;

    protected function setUp(): void
    {
        $this->itexmo = new ItexmoSms([
            'api_code' => 'api_code_here',
            'email' => 'email_here',
            'password' => 'password_here'
        ]);
    }

    public function testBroadcast()
    {
        $response = $this->itexmo->broadcast('recipient_number', 'Test message');
        var_dump($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success'], 'The message was not sent successfully.');
    }

    public function testQueryBalance()
    {
        $response = $this->itexmo->query('balance');
        var_dump($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success'], 'The query for balance was unsuccessful.');
    }

    public function testBroadcastWithMultipleRecipients()
    {
        $recipients = ['recipient1_number', 'recipient2_number']; // Replace with valid numbers
        $response = $this->itexmo->broadcast($recipients, 'Test message to multiple recipients');
        var_dump($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success'], 'The messages were not sent successfully.');
    }

    public function testBroadcastWithInvalidNumber()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->itexmo->broadcast('invalid_number', 'Test message');
    }

    public function testQueryBalanceWithInvalidQuery()
    {
        $response = $this->itexmo->query('invalid_query');
        var_dump($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success'], 'The query for an invalid command should fail.');
    }

    public function testBroadcastLengthValidation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->itexmo->broadcast('recipient_number', str_repeat('A', 161)); // Assuming max length is 160
    }

    public function testBroadcastOTP()
    {
        $response = $this->itexmo->broadcastOTP('recipient_number', 'Your OTP is 123456');
        var_dump($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success'], 'The OTP message was not sent successfully.');
    }

    public function testBroadcastOTPWithInvalidNumber()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->itexmo->broadcastOTP('invalid_number', 'Your OTP is 123456');
    }
}
