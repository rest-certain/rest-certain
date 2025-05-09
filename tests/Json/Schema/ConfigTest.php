<?php

declare(strict_types=1);

namespace RestCertain\Test\Json\Schema;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Opis\JsonSchema\Uri as JsonSchemaUri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use RestCertain\Config as RestCertainConfig;
use RestCertain\Exception\JsonSchemaFailure;
use RestCertain\Json\Schema\Config;
use RuntimeException;
use stdClass;

use function assert;

class ConfigTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testConfig(): void
    {
        $myProtocolCalled = 0;
        $myProtocolUriReceived = null;
        $myOtherProtocolCalled = 0;
        $myOtherProtocolUriReceived = null;
        $firstUri = 'http://example.com/schema/foo.json';
        $secondUri = 'https://example.net/schema/bar.json';

        $httpClient = Mockery::mock(ClientInterface::class);

        $httpClient
            ->shouldReceive('sendRequest')
            ->with(Mockery::on(fn (RequestInterface $request) => (string) $request->getUri() === $firstUri))
            ->andReturns(Mockery::mock(ResponseInterface::class, [
                'getStatusCode' => 200,
                'getBody->getContents' => '{"foo":123}',
            ]));

        $httpClient
            ->shouldReceive('sendRequest')
            ->with(Mockery::on(fn (RequestInterface $request) => (string) $request->getUri() === $secondUri))
            ->andReturns(Mockery::mock(ResponseInterface::class, [
                'getStatusCode' => 200,
                'getBody->getContents' => '{"bar":456}',
            ]));

        $restCertainConfig = new RestCertainConfig(httpClient: $httpClient);

        $config = new Config(
            $restCertainConfig,
            [
                'my-protocol' => function (UriInterface $uri) use (
                    &$myProtocolCalled,
                    &$myProtocolUriReceived,
                ): stdClass {
                    $myProtocolCalled++;
                    $myProtocolUriReceived = (string) $uri;

                    return (object) ['baz' => 789];
                },
                'my-other-protocol' => function (UriInterface $uri) use (
                    &$myOtherProtocolCalled,
                    &$myOtherProtocolUriReceived,
                ): stdClass {
                    $myOtherProtocolCalled++;
                    $myOtherProtocolUriReceived = (string) $uri;

                    return (object) ['qux' => 345];
                },
            ],
            [
                'prefixA' => '/path/to/files',
                'prefixB' => '/path/to/other/files',
            ],
        );

        $uri1 = JsonSchemaUri::create($firstUri);
        assert($uri1 !== null);

        $uri2 = JsonSchemaUri::create($secondUri);
        assert($uri2 !== null);

        $uri3 = JsonSchemaUri::create('my-protocol://foo.json');
        assert($uri3 !== null);

        $uri4 = JsonSchemaUri::create('my-other-protocol://bar.json');
        assert($uri4 !== null);

        $this->assertEquals((object) ['foo' => 123], $config->resolver->resolve($uri1));
        $this->assertEquals((object) ['bar' => 456], $config->resolver->resolve($uri2));
        $this->assertEquals((object) ['baz' => 789], $config->resolver->resolve($uri3));
        $this->assertEquals((object) ['qux' => 345], $config->resolver->resolve($uri4));
        $this->assertSame(1, $myProtocolCalled);
        $this->assertSame('my-protocol://foo.json', $myProtocolUriReceived);
        $this->assertSame(1, $myOtherProtocolCalled);
        $this->assertSame('my-other-protocol://bar.json', $myOtherProtocolUriReceived);
    }

    public function testHttpResolverThrowsExceptionOnClientError(): void
    {
        $exception = new class extends RuntimeException implements ClientExceptionInterface {
            /** @var string */
            // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
            protected $message = 'HTTP was naughty. Bad HTTP!';
        };

        $httpClient = Mockery::mock(ClientInterface::class);
        $httpClient->shouldReceive('sendRequest')->andThrows($exception);

        $restCertainConfig = new RestCertainConfig(httpClient: $httpClient);

        $config = new Config($restCertainConfig);

        $uri = JsonSchemaUri::create('https://example.com/schema/foo.json');
        assert($uri !== null);

        $this->expectException(JsonSchemaFailure::class);
        $this->expectExceptionMessage(
            'Encountered an error while attempting to fetch the JSON Schema from https://example.com/schema/foo.json: '
            . 'HTTP was naughty. Bad HTTP!',
        );

        $config->resolver->resolve($uri);
    }

    public function testHttpResolverThrowsExceptionOnBadStatusCode(): void
    {
        $httpClient = Mockery::mock(ClientInterface::class);
        $httpClient
            ->shouldReceive('sendRequest')
            ->andReturns(Mockery::mock(ResponseInterface::class, [
                'getStatusCode' => 400,
                'getBody->getContents' => 'Something is wrong.',
            ]));

        $restCertainConfig = new RestCertainConfig(httpClient: $httpClient);

        $config = new Config($restCertainConfig);

        $uri = JsonSchemaUri::create('https://example.com/schema/foo.json');
        assert($uri !== null);

        $this->expectException(JsonSchemaFailure::class);
        $this->expectExceptionMessage(
            'Received status code 400 when attempting to fetch the JSON Schema from '
            . "https://example.com/schema/foo.json\n\nSomething is wrong.\n",
        );

        $config->resolver->resolve($uri);
    }

    public function testHttpResolverThrowsExceptionOnBadStatusCodeWithNoBody(): void
    {
        $httpClient = Mockery::mock(ClientInterface::class);
        $httpClient
            ->shouldReceive('sendRequest')
            ->andReturns(Mockery::mock(ResponseInterface::class, [
                'getStatusCode' => 204,
                'getBody->getContents' => '',
            ]));

        $restCertainConfig = new RestCertainConfig(httpClient: $httpClient);

        $config = new Config($restCertainConfig);

        $uri = JsonSchemaUri::create('https://example.com/schema/foo.json');
        assert($uri !== null);

        $this->expectException(JsonSchemaFailure::class);
        $this->expectExceptionMessage(
            'Received status code 204 when attempting to fetch the JSON Schema from '
            . 'https://example.com/schema/foo.json',
        );

        $config->resolver->resolve($uri);
    }

    public function testHttpResolverThrowsExceptionWhenJsonDoesNotDecodeAsObject(): void
    {
        $httpClient = Mockery::mock(ClientInterface::class);
        $httpClient
            ->shouldReceive('sendRequest')
            ->andReturns(Mockery::mock(ResponseInterface::class, [
                'getStatusCode' => 200,
                'getBody->getContents' => '"This is a JSON string."',
            ]));

        $restCertainConfig = new RestCertainConfig(httpClient: $httpClient);

        $config = new Config($restCertainConfig);

        $uri = JsonSchemaUri::create('https://example.com/schema/foo.json');
        assert($uri !== null);

        $this->expectException(JsonSchemaFailure::class);
        $this->expectExceptionMessage(
            "JSON Schema did not decode to an object.\n\n"
            . "Schema URI: https://example.com/schema/foo.json\n"
            . "Decoded value: 'This is a JSON string.'\n",
        );

        $config->resolver->resolve($uri);
    }
}
