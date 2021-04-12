<?php

namespace Vlnic\Slimness\Test;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;
use Vlnic\Slimness\Client;
use Vlnic\Slimness\Exceptions\ClientException;

/**
 * Class ClientTest
 * @package Vlnic\Slimness\Test
 */
class ClientTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     *
     * @param Client $client
     * @param MockObject $mock
     */
    public function testPostJsonFail(Client $client, MockObject $mock)
    {
        $mock->method('request')
            ->willThrowException(
                new RequestException('Method is not allowed',
                    new Request('POST', 'http://some.local'), new Response(400, [], '{"field": "someField"}')
                )
            );
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('request error: code: 400, body: {"field": "someField"}');
        $client->addOptions(['allow_redirects' => false, 'connect_timeout' => 30])
            ->ifError(function (int $code, string $body, Throwable $e) {
                throw new ClientException(sprintf('request error: code: %s, body: %s', $code, $body));
            })->postJson('http://some.local', []);
    }

    /**
     * @return array[]
     */
    public function dataProvider() : array
    {
        $mock = $this->mockClient();
        $childClient = new class ($mock) extends Client {
            public function __construct(Client $client)
            {
                parent::__construct();
                $this->client = $client;
            }
        };
        return [
            [$childClient, $mock]
        ];
    }

    /**
     * @return MockObject
     */
    protected function mockClient() : MockObject
    {
        return $this->getMockBuilder(Client::class)
            ->addMethods(['request'])
            ->getMock();
    }
}