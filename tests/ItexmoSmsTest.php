<?php

namespace Agnes\ItexmoSms\Tests;

use Agnes\ItexmoSms\ItexmoSms;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ItexmoSmsTest extends TestCase
{
    protected $itexmo;

    protected function setUp(): void
    {
        $this->itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
    }

    public function testConstructorWithCustomConfig()
    {
        $customItexmo = new ItexmoSms(['api_code' => 'custom_api_code']);
        $this->assertInstanceOf(ItexmoSms::class, $customItexmo);
    }

    /**
     * @dataProvider endpointProvider
     */
    public function testSuccessfulRequest($method, $args)
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 0]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = call_user_func_array([$this->itexmo, $method], $args);
        $this->assertEquals([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data' => ['status' => 0],
        ], $response);
    }

    /**
     * @dataProvider endpointProvider
     */
    public function testAllErrorStatusCodes($method, $args)
    {
        $errorCodes = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $errorMessages = [
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

        foreach ($errorCodes as $code) {
            $mock = new MockHandler([
                new Response(200, [], json_encode(['status' => $code]))
            ]);

            $handlerStack = HandlerStack::create($mock);
            $client = new Client(['handler' => $handlerStack]);

            $this->itexmo->setClient($client);

            $response = call_user_func_array([$this->itexmo, $method], $args);
            $this->assertEquals([
                'success' => false,
                'message' => $errorMessages[$code],
                'data' => ['status' => $code]
            ], $response);
        }
    }

    /**
     * @dataProvider endpointProvider
     */
    public function testUnauthorizedRequest($method, $args)
    {
        $mock = new MockHandler([
            new Response(401, [])
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = call_user_func_array([$this->itexmo, $method], $args);
        $this->assertEquals([
            'success' => false,
            'message' => 'Unauthorized request. Check your API Key.',
            'data' => null
        ], $response);
    }

    /**
     * @dataProvider endpointProvider
     */
    public function testInvalidApiResponse($method, $args)
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['invalid' => 'response']))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = call_user_func_array([$this->itexmo, $method], $args);
        $this->assertEquals([
            'success' => false,
            'message' => 'Invalid API response',
            'data' => ['invalid' => 'response']
        ], $response);
    }

    /**
     * @dataProvider endpointProvider
     */
    public function testUnrecognizedStatusCode($method, $args)
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 999]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = call_user_func_array([$this->itexmo, $method], $args);
        $this->assertEquals([
            'success' => false,
            'message' => 'Unrecognized status code.',
            'data' => ['status' => 999]
        ], $response);
    }

    public function endpointProvider()
    {
        return [
            'broadcast' => ['broadcast', [['1234567890'], 'Test message']],
            'broadcast2d' => ['broadcast2d', [[['1234567890', 'Test message']]]],
            'broadcastOTP' => ['broadcastOTP', ['1234567890', 'Your OTP is 123456']],
            'query' => ['query', ['balance']],
        ];
    }



    /**
     * @dataProvider multipleRecipientsProvider
     */

    public function testMultipleRecipients($method, $args)
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 0])) // Simulating success
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = call_user_func_array([$this->itexmo, $method], $args);
        $this->assertEquals([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data' => ['status' => 0]
        ], $response);
    }



    /**
     * Data provider for multiple recipients test.
     * @return array[]
     */
    public function multipleRecipientsProvider()
    {
        return [
            // broadcast: sending the same message to multiple recipients
            'broadcast' => [
                'broadcast', 
                [['1234567890', '0987654321'], 'Test message to multiple recipients']
            ],

            // broadcast 2D: sending different messages to multiple recipients
            'broadcast2d' => [
                'broadcast2d', 
                [[
                    ['1234567890', 'Test message 1'],
                    ['0987654321', 'Test message 2']
                ]]
            ],

        ];
    }

}