<?php

declare(strict_types=1);

namespace RestCertain\Test;

use Http\Discovery\Psr17Factory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message;
use RestCertain\Config;
use RestCertain\Http\HttpFactory;
use Stringable;

use function assert;
use function get_object_vars;
use function is_object;
use function is_string;
use function ucfirst;

class ConfigTest extends TestCase
{
    use MockeryPHPUnitIntegration;

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
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(Message\RequestFactoryInterface::class);
        $responseFactory = Mockery::mock(Message\ResponseFactoryInterface::class);
        $streamFactory = Mockery::mock(Message\StreamFactoryInterface::class);
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

    #[DataProvider('witherProvider')]
    public function testWithers(string $property, mixed $value): void
    {
        $wither = 'with' . ucfirst($property);

        $oldConfig = new Config();

        /** @var Config $newConfig */
        $newConfig = $oldConfig->{$wither}($value);

        if ($property === 'baseUri' && !$value instanceof Message\UriInterface) {
            assert(is_string($value) || $value instanceof Stringable);
            $this->assertSame((string) $value, (string) $newConfig->baseUri);
        } else {
            $this->assertSame($value, $newConfig->{$property});
        }

        $this->assertNotSame($oldConfig, $newConfig);
        $this->assertCopiedProperties($property, $oldConfig, $newConfig);
    }

    /**
     * @return array<array{property: string, value: mixed}>
     */
    public static function witherProvider(): array
    {
        return [
            [
                'property' => 'baseUri',
                'value' => new Uri('https://api.example.com'),
            ],
            [
                'property' => 'baseUri',
                'value' => new Str('https://api.example.net:8080'),
            ],
            [
                'property' => 'baseUri',
                'value' => 'https://api.example.org',
            ],
            [
                'property' => 'basePath',
                'value' => '/foo/bar',
            ],
            [
                'property' => 'port',
                'value' => 9000,
            ],
            [
                'property' => 'httpClient',
                'value' => Mockery::mock(ClientInterface::class),
            ],
            [
                'property' => 'requestFactory',
                'value' => Mockery::mock(Message\RequestFactoryInterface::class),
            ],
            [
                'property' => 'responseFactory',
                'value' => Mockery::mock(Message\ResponseFactoryInterface::class),
            ],
            [
                'property' => 'streamFactory',
                'value' => Mockery::mock(Message\StreamFactoryInterface::class),
            ],
            [
                'property' => 'uriFactory',
                'value' => Mockery::mock(Message\UriFactoryInterface::class),
            ],
        ];
    }

    private function assertCopiedProperties(string $changedProperty, Config $oldConfig, Config $newConfig): void
    {
        $propertiesAsserted = 0;
        foreach (get_object_vars($oldConfig) as $key => $value) {
            if (is_object($value)) {
                $this->assertNotSame($value, $newConfig->$key);
            }

            if ($key !== $changedProperty) {
                // If the property hasn't changed, they should be equal but not identical because they were cloned.
                $this->assertEquals($value, $newConfig->$key);
                $propertiesAsserted++;
            }
        }

        // Let's ensure that we actually looped over and tested the properties.
        $this->assertSame(7, $propertiesAsserted);
    }
}
