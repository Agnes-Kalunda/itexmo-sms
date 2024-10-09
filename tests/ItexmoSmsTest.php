<?php

namespace Agnes\ItexmoSms\Tests;

use Agnes\ItexmoSms\ItexmoSms;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ItexmoSmsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testBroadcastSuccess()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 'SUCCESS']))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($client);

        $response = $itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertEquals('SUCCESS', $response['status']);
    }

    public function testUnauthorizedError()
    {
        $mock = new MockHandler([
            new Response(401, [], null)
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($client);

        $response = $itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertNull($response);
    }

    public function testNetworkError()
    {
        $mock = new MockHandler([
            new RequestException('Network error', $this->createMock(RequestInterface::class))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($client);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HTTP request error');

        $itexmo->broadcast(['1234567890'], 'Test message');
    }
}