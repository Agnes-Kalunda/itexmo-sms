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
        // dummy API code for testing
        $this->itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
    }

    public function testSuccessfulSmsSending()
    {
        // mock successful API response with status 0
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 0]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertEquals([
            'success'=> true,
            'message'=> 'Message sent successfully',
            'data'=> ['status'=>0],
        ], $response);
    }

    public function testInvalidNumberError()
    {
        // mock response with status 1 (invalid number)
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 1]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['invalid_number'], 'Test message');
        $this->assertEquals([
            'success'=> false,
            'message'=> 'Invalid Number',
            'data'=> ['status'=> 1],
        ], $response);
    }


    

    public function testInsufficientCreditError()
    {
        // mock response with status 2 (insufficient credit)
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 2]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertEquals('ERROR: No balance or insufficient credit.', $response);
    }

    public function testInvalidApiCodeError()
    {
        // mock response with status 3 (invalid API code)
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 3]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertEquals('ERROR: Invalid API code.', $response);
    }

    public function testMaximumCharactersExceededError()
    {
        // mock response with status 4 (maximum characters exceeded)
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 4]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['1234567890'], 'Test message exceeds character limit');
        $this->assertEquals('ERROR: Maximum number of characters exceeded.', $response);
    }

    public function testSmsBlockedDueToSpamError()
    {
        // mock response with status 5 (SMS blocked due to spam content)
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 5]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['1234567890'], 'Spam message');
        $this->assertEquals('ERROR: SMS is blocked due to spam content.', $response);
    }

    public function testInvalidSenderNameError()
    {
        // mock response with status 6 (invalid sender name)
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 6]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertEquals('ERROR: Invalid sender name.', $response);
    }

    public function testInvalidMobileNumberFormatError()
    {
        // mockresponse with status 7 (invalid mobile number format)
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 7]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['123'], 'Test message');
        $this->assertEquals('ERROR: Invalid mobile number format.', $response);
    }

    public function testUnauthorizedRequestError()
    {
        // mock response with status 8 (unauthorized request)
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 8]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertEquals('ERROR: Unauthorized request or API not allowed.', $response);
    }

    public function testApiDeactivatedError()
    {
        // mockresponse with status 9 (API deactivated)
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 9]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertEquals('ERROR: API deactivated.', $response);
    }

    public function testUnhandledApiResponse()
    {
        // mock response with unknown status
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 999]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->itexmo->setClient($client);

        $response = $this->itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertEquals('ERROR: Unrecognized status code.', $response);
    }
}

