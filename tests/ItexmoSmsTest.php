<?php

namespace Tests;

use Agnes\ItexmoSms\ItexmoSms;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\TestCase;
use Mockery;

class ItexmoSmsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testBroadcastSuccess()
    {
        $clientMock = Mockery::mock(Client::class);
        $clientMock->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200, [], json_encode(['status' => 'SUCCESS'])));

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($clientMock);

        $response = $itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertEquals('SUCCESS', $response['status']);
    }

    public function testUnauthorizedError()
    {
        $clientMock = Mockery::mock(Client::class);
        $clientMock->shouldReceive('post')
            ->once()
            ->andReturn(new Response(401, [], null));

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($clientMock);

        $response = $itexmo->broadcast(['1234567890'], 'Test message');
        
        // Fix: Check for null before accessing the array
        $this->assertNull($response);
    }

    public function testNetworkError()
    {
        $clientMock = Mockery::mock(Client::class);
        $requestMock = Mockery::mock(Request::class); // Create a mock request

        $clientMock->shouldReceive('post')
            ->once()
            ->andThrow(new RequestException('Network error', $requestMock));

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($clientMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HTTP request error');

        $itexmo->broadcast(['1234567890'], 'Test message');
    }

    public function testUnexpectedStatusCode()
    {
        $clientMock = Mockery::mock(Client::class);
        $clientMock->shouldReceive('post')
            ->once()
            ->andReturn(new Response(500, [], null));

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($clientMock);

        $response = $itexmo->broadcast(['1234567890'], 'Test message');

        $this->assertNull($response);
    }

    public function testTimeoutError()
    {
        $clientMock = Mockery::mock(Client::class);
        $requestMock = Mockery::mock(Request::class); // Create a mock request

        $clientMock->shouldReceive('post')
            ->once()
            ->andThrow(new ConnectException('Timeout error', $requestMock));

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($clientMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Network connection error');

        $itexmo->broadcast(['1234567890'], 'Test message');
    }

    public function testInvalidJsonResponse()
    {
        $clientMock = Mockery::mock(Client::class);
        $clientMock->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200, [], 'Invalid JSON'));

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($clientMock);

        $response = $itexmo->broadcast(['1234567890'], 'Test message');

        $this->assertNull($response);
    }
}
