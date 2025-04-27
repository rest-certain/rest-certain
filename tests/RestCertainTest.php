<?php

declare(strict_types=1);

namespace RestCertain\Test;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use ReflectionFunction;
use RestCertain\Config;
use RestCertain\Internal\RequestSpecificationImpl;
use RestCertain\Internal\ResponseImpl;
use RestCertain\RestCertain;
use Stringable;

use function RestCertain\given;
use function RestCertain\request;
use function RestCertain\when;
use function RestCertain\with;

class RestCertainTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ClientInterface $client;

    protected function setUp(): void
    {
        $stream = Mockery::mock(StreamInterface::class);
        $stream->allows('getContents')->andReturns('Hello, World!');

        $response = Mockery::mock(ResponseInterface::class);
        $response->allows('getBody')->andReturns($stream);
        $response->allows('getHeader')->with('Set-Cookie')->andReturns([]);

        $this->client = Mockery::mock(ClientInterface::class);
        $this->client->allows('sendRequest')->andReturns($response);

        RestCertain::$config = new Config(basePath: '/foo', httpClient: $this->client);
    }

    protected function tearDown(): void
    {
        RestCertain::$config = null;
    }

    /**
     * @param array<string, Stringable | int | string> $pathParams
     */
    #[DataProvider('methodProvider')]
    public function testMethod(
        string $method,
        Stringable | UriInterface | string $uri,
        array $pathParams,
        string $expectedUri,
    ): void {
        $response = RestCertain::{$method}($uri, $pathParams);
        $response2 = RestCertain::request($method, $uri, $pathParams);

        $functionName = 'RestCertain\\' . $method;
        $reflectedFunction = new ReflectionFunction($functionName);
        $response3 = $reflectedFunction->invoke($uri, $pathParams);

        $response4 = request($method, $uri, $pathParams);

        $this->assertInstanceOf(ResponseImpl::class, $response);
        $this->assertSame($expectedUri, (string) $response->getPsrRequest()->getUri());

        $this->assertInstanceOf(ResponseImpl::class, $response2);
        $this->assertSame($expectedUri, (string) $response2->getPsrRequest()->getUri());

        $this->assertInstanceOf(ResponseImpl::class, $response3);
        $this->assertSame($expectedUri, (string) $response3->getPsrRequest()->getUri());

        $this->assertInstanceOf(ResponseImpl::class, $response4);
        $this->assertSame($expectedUri, (string) $response4->getPsrRequest()->getUri());
    }

    /**
     * @return array<array{
     *     method: string,
     *     uri: Stringable | UriInterface | string,
     *     pathParams: array<string, Stringable | int | string>,
     *     expectedUri: string,
     * }>
     */
    public static function methodProvider(): array
    {
        return [
            [
                'method' => 'delete',
                'uri' => new Str('/{entity}/{id}/{type}'),
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'delete',
                'uri' => new Uri('https://example.com/user/123/messages'),
                'pathParams' => [],
                'expectedUri' => 'https://example.com/user/123/messages',
            ],
            [
                'method' => 'delete',
                'uri' => '/{entity}/{id}/{type}',
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'get',
                'uri' => new Str('/{entity}/{id}/{type}'),
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'get',
                'uri' => new Uri('https://example.com/user/123/messages'),
                'pathParams' => [],
                'expectedUri' => 'https://example.com/user/123/messages',
            ],
            [
                'method' => 'get',
                'uri' => '/{entity}/{id}/{type}',
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'head',
                'uri' => new Str('/{entity}/{id}/{type}'),
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'head',
                'uri' => new Uri('https://example.com/user/123/messages'),
                'pathParams' => [],
                'expectedUri' => 'https://example.com/user/123/messages',
            ],
            [
                'method' => 'head',
                'uri' => '/{entity}/{id}/{type}',
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'options',
                'uri' => new Str('/{entity}/{id}/{type}'),
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'options',
                'uri' => new Uri('https://example.com/user/123/messages'),
                'pathParams' => [],
                'expectedUri' => 'https://example.com/user/123/messages',
            ],
            [
                'method' => 'options',
                'uri' => '/{entity}/{id}/{type}',
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'patch',
                'uri' => new Str('/{entity}/{id}/{type}'),
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'patch',
                'uri' => new Uri('https://example.com/user/123/messages'),
                'pathParams' => [],
                'expectedUri' => 'https://example.com/user/123/messages',
            ],
            [
                'method' => 'patch',
                'uri' => '/{entity}/{id}/{type}',
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'post',
                'uri' => new Str('/{entity}/{id}/{type}'),
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'post',
                'uri' => new Uri('https://example.com/user/123/messages'),
                'pathParams' => [],
                'expectedUri' => 'https://example.com/user/123/messages',
            ],
            [
                'method' => 'post',
                'uri' => '/{entity}/{id}/{type}',
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'put',
                'uri' => new Str('/{entity}/{id}/{type}'),
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
            [
                'method' => 'put',
                'uri' => new Uri('https://example.com/user/123/messages'),
                'pathParams' => [],
                'expectedUri' => 'https://example.com/user/123/messages',
            ],
            [
                'method' => 'put',
                'uri' => '/{entity}/{id}/{type}',
                'pathParams' => ['entity' => 'user', 'id' => 123, 'type' => new Str('messages')],
                'expectedUri' => 'http://localhost:8000/foo/user/123/messages',
            ],
        ];
    }

    public function testGiven(): void
    {
        RestCertain::$config = null;

        $this->assertInstanceOf(RequestSpecificationImpl::class, RestCertain::given());
        $this->assertInstanceOf(RequestSpecificationImpl::class, given());
    }

    public function testWhen(): void
    {
        RestCertain::$config = null;

        $this->assertInstanceOf(RequestSpecificationImpl::class, RestCertain::when());
        $this->assertInstanceOf(RequestSpecificationImpl::class, when());
    }

    public function testWith(): void
    {
        RestCertain::$config = null;

        $this->assertInstanceOf(RequestSpecificationImpl::class, RestCertain::with());
        $this->assertInstanceOf(RequestSpecificationImpl::class, with());
    }
}
