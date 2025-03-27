<?php

declare(strict_types=1);

namespace RestCertain\Test;

use Http\Discovery\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message;
use RestCertain\Config;
use RestCertain\Http\HttpFactory;

class ConfigTest extends TestCase
{
    public function testConfigWithDefaults(): void
    {
        $config = new Config();

        $this->assertSame('http://localhost', (string) $config->baseUri);
        $this->assertSame('/', $config->basePath);
        $this->assertSame(8000, $config->port);
        $this->assertInstanceOf(HttpFactory::class, $config->httpClient);
        $this->assertInstanceOf(HttpFactory::class, $config->requestFactory);
        $this->assertInstanceOf(HttpFactory::class, $config->responseFactory);
        $this->assertInstanceOf(HttpFactory::class, $config->streamFactory);
        $this->assertInstanceOf(HttpFactory::class, $config->uriFactory);
    }

    public function testConfigWithCustomValues(): void
    {
        $httpClient = $this->mockery(ClientInterface::class);
        $requestFactory = $this->mockery(Message\RequestFactoryInterface::class);
        $responseFactory = $this->mockery(Message\ResponseFactoryInterface::class);
        $streamFactory = $this->mockery(Message\StreamFactoryInterface::class);
        $uriFactory = new Psr17Factory();

        $config = new Config(
            'https://api.example.net',
            '/foo',
            8080,
            $httpClient,
            $requestFactory,
            $responseFactory,
            $streamFactory,
            $uriFactory,
        );

        $this->assertSame('https://api.example.net', (string) $config->baseUri);
        $this->assertSame('/foo', $config->basePath);
        $this->assertSame(8080, $config->port);
        $this->assertSame($httpClient, $config->httpClient);
        $this->assertSame($requestFactory, $config->requestFactory);
        $this->assertSame($responseFactory, $config->responseFactory);
        $this->assertSame($streamFactory, $config->streamFactory);
        $this->assertSame($uriFactory, $config->uriFactory);
    }
}
