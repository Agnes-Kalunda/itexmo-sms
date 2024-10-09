<?php

namespace Agnes\ItexmoSms\Tests;

use Agnes\ItexmoSms\ItexmoSms;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ItexmoSmsTest extends TestCase
{
    public function testBroadcastSuccess()
    {
        /** @var Client|\PHPUnit\Framework\MockObject\MockObject $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('post')
            ->willReturn(new Response(200, [], json_encode(['status' => 'SUCCESS'])));

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($clientMock);

        $response = $itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertEquals('SUCCESS', $response['status']);
    }

    public function testUnauthorizedError()
    {
        /** @var Client|\PHPUnit\Framework\MockObject\MockObject $clientMock */
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('post')
            ->willReturn(new Response(401, [], null));

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($clientMock);

        $response = $itexmo->broadcast(['1234567890'], 'Test message');
        $this->assertNull($response);
    }

    public function testNetworkError()
    {
        /** @var Client|\PHPUnit\Framework\MockObject\MockObject $clientMock */
        $clientMock = $this->createMock(Client::class);
        /** @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject $requestMock */
        $requestMock = $this->createMock(RequestInterface::class);
        $clientMock->method('post')
            ->willThrowException(new RequestException('Network error', $requestMock));

        $itexmo = new ItexmoSms(['api_code' => 'test_api_code']);
        $itexmo->setClient($clientMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HTTP request error');

        $itexmo->broadcast(['1234567890'], 'Test message');
    }

    
}