<?php

namespace Itexmo\ItexmoSms\Tests;

use Itexmo\ItexmoSms\ItexmoSms;
use Itexmo\ItexmoSms\ValidationException;
use Itexmo\ItexmoSms\ApiException;
use Itexmo\ItexmoSms\NetworkException;
use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;

class ItexmoSmsIntegrationTest extends TestCase
{
    protected $itexmo;
    protected $validPhoneNumber;
    protected $validPhoneNumber2;

    protected function setUp(): void
    {
        $dotenv = Dotenv::create(__DIR__ . '/..');
        $dotenv->load();

        $this->validPhoneNumber = getenv('TEST_PHONE_NUMBER');
        $this->validPhoneNumber2 = getenv('TEST_PHONE_NUMBER1');

        $this->itexmo = new ItexmoSms([
            'api_code' => getenv('ITEXMO_API_CODE'),
            'email' => getenv('ITEXMO_EMAIL'),
            'password' => getenv('ITEXMO_PASSWORD'),
        ]);
    }

    public function testConstructorValidation()
    {
        $this->expectException(ValidationException::class);
        new ItexmoSms([]);  // empty config

        $this->expectException(ValidationException::class);
        new ItexmoSms(['api_code' => 'test']);  // missing email and password

        $this->expectException(ValidationException::class);
        new ItexmoSms([
            'api_code' => 'test',
            'email' => 'invalid-email',
            'password' => 'test'
        ]);  // invalid email format
    }

    public function testCheckBalance()
    {
        $response = $this->itexmo->checkBalance();
        
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('data', $response);
        
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('MessagesLeft', $response['data']);
    }

    public function testBroadcastValidation()
    {
        // empty recipients
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcast([], 'Test message');

        // invalid phone number
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcast(['123'], 'Test message');

        // empty message
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcast([$this->validPhoneNumber], '');

        // message too long
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcast(
            [$this->validPhoneNumber],
            str_repeat('x', 161)  // 161 characters
        );
    }

    public function testSuccessfulBroadcast()
    {
        $response = $this->itexmo->broadcast(
            [$this->validPhoneNumber, $this->validPhoneNumber2],
            'Test broadcast message'
        );
        
        $this->assertTrue($response['success']);
        $this->assertEquals(0, $response['data']['Failed'] ?? -1);
        $this->assertEquals(2, $response['data']['TotalSMS'] ?? 0);
    }

    public function testBroadcastOTPValidation()
    {
        //empty recipients
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcastOTP([], 'Your OTP is 123456');

        // invalid phone number
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcastOTP(['invalid-number'], 'Your OTP is 123456');

        // empty message
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcastOTP([$this->validPhoneNumber], '');
    }

    public function testSuccessfulBroadcastOTP()
    {
        $response = $this->itexmo->broadcastOTP(
            [$this->validPhoneNumber],
            'Your OTP is 123456'
        );
        
        $this->assertTrue($response['success']);
        $this->assertEquals(0, $response['data']['Failed'] ?? -1);
    }

    public function testBroadcast2dValidation()
    {
        // empty messages array
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcast2d([]);

        // invalid message format
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcast2d([['invalid']]);  // missing message

        //invalid phone number
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcast2d([
            ['invalid-number', 'Test message']
        ]);

        // empty message
        $this->expectException(ValidationException::class);
        $this->itexmo->broadcast2d([
            [$this->validPhoneNumber, '']
        ]);
    }

    public function testSuccessfulBroadcast2d()
    {
        $messages = [
            [$this->validPhoneNumber, 'Test message 1'],
            [$this->validPhoneNumber2, 'Test message 2'],
        ];
        
        $response = $this->itexmo->broadcast2d($messages);
        
        $this->assertTrue($response['success']);
        $this->assertEquals(0, $response['data']['Failed'] ?? -1);
        $this->assertEquals(2, $response['data']['TotalSMS'] ?? 0);
    }

    public function testInvalidCredentials()
    {
        $this->expectException(ApiException::class);
        
        $itexmo = new ItexmoSms([
            'api_code' => 'invalid',
            'email' => 'test@example.com',
            'password' => 'invalid'
        ]);
        
        $itexmo->checkBalance();
    }

    /**
     * @group network
     */
    public function testNetworkTimeout()
    {
        $this->expectException(NetworkException::class);
        
        $itexmo = new ItexmoSms([
            'api_code' => getenv('ITEXMO_API_CODE'),
            'email' => getenv('ITEXMO_EMAIL'),
            'password' => getenv('ITEXMO_PASSWORD'),
            'timeout' => 0.001  // Very short timeout
        ]);
        
        $itexmo->checkBalance();
    }
}