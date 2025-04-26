<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Internal\NotImplemented;
use RestCertain\Internal\ResponseBodyImpl;
use RestCertain\Internal\ResponseImpl;
use RestCertain\Internal\ValidatableResponseOptionsImpl;

class ResponseImplTest extends TestCase
{
    private ResponseInterface & MockInterface $psrResponse;
    private ResponseImpl $response;
    private StreamInterface & MockInterface $stream;

    protected function setUp(): void
    {
        $this->stream = Mockery::mock(StreamInterface::class);
        $this->psrResponse = Mockery::spy(ResponseInterface::class, [
            'getBody' => $this->stream,
        ]);
        $this->psrResponse->allows('getHeader')->with('Set-Cookie')->andReturns([
            'sessionId=38afes7a8',
            'id=a3fWa; Max-Age=2592000; SameSite=Lax',
            'sessionId=e8bb43229de9; Domain=foo.example.com',
            '__Host-ID=123; Secure; Path=/',
            '__Secure-ID=123; Secure; Domain=example.com',
            '__Host-example=34d8g; SameSite=None; Secure; Path=/; Partitioned;',
        ]);

        $this->response = new ResponseImpl($this->psrResponse);
    }

    public function testAndReturn(): void
    {
        $this->assertSame($this->response, $this->response->andReturn());
    }

    public function testAsPrettyString(): void
    {
        $this->markTestIncomplete('Need to implement ' . ResponseImpl::class . '::asPrettyString()');
    }

    public function testAsString(): void
    {
        $this->stream->expects('rewind')->once();
        $this->stream->expects('getContents')->once()->andReturns('Hello, World!');

        $this->assertSame('Hello, World!', $this->response->asString());
    }

    public function testBody(): void
    {
        $body = $this->response->body();

        $this->assertInstanceOf(ResponseBodyImpl::class, $body);
        $this->assertSame($body, $this->response->getBody());
    }

    public function testContentType(): void
    {
        $this->psrResponse->allows('hasHeader')->with('content-type')->andReturns(true);
        $this->psrResponse->allows('getHeaderLine')->with('content-type')->andReturns('text/html');

        $this->assertSame('text/html', $this->response->contentType());
        $this->assertSame('text/html', $this->response->getContentType());
    }

    public function testContentTypeReturnsNull(): void
    {
        $this->psrResponse->allows('hasHeader')->with('content-type')->andReturns(false);

        $this->assertNull($this->response->contentType());
        $this->assertNull($this->response->getContentType());
    }

    public function testCookie(): void
    {
        $this->assertSame('e8bb43229de9', $this->response->cookie('sessionId'));
        $this->assertSame('e8bb43229de9', $this->response->getCookie('sessionId'));
    }

    public function testCookies(): void
    {
        $expected = [
            'sessionId' => 'e8bb43229de9',
            'id' => 'a3fWa',
            '__Host-ID' => '123',
            '__Secure-ID' => '123',
            '__Host-example' => '34d8g',
        ];

        $this->assertSame($expected, $this->response->cookies());
        $this->assertSame($expected, $this->response->getCookies());
    }

    public function testHeader(): void
    {
        $expected = [
            '<https://api.example.com/resource?page=1>; rel="self"',
            '<https://api.example.com/resource?page=2>; rel="next"',
            '<https://api.example.com/resource?page=5>; rel="last"',
        ];

        $this->psrResponse->allows('getHeader')->with('link')->andReturns($expected);

        $this->assertSame($expected, $this->response->header('link'));
        $this->assertSame($expected, $this->response->getHeader('link'));
    }

    public function testGetHeaderLine(): void
    {
        $this->psrResponse->allows('getHeaderLine')->with('my-header')->andReturns('my header line');

        $this->assertSame('my header line', $this->response->getHeaderLine('my-header'));
    }

    public function testHeaders(): void
    {
        $expected = [
            'link' => [
                '<https://api.example.com/resource?page=1>; rel="self"',
                '<https://api.example.com/resource?page=2>; rel="next"',
                '<https://api.example.com/resource?page=5>; rel="last"',
            ],
            'my-header' => ['my header line'],
        ];

        $this->psrResponse->allows('getHeaders')->andReturns($expected);

        $this->assertSame($expected, $this->response->headers());
        $this->assertSame($expected, $this->response->getHeaders());
    }

    public function testGetProtocolVersion(): void
    {
        $this->psrResponse->allows('getProtocolVersion')->andReturns('1.1');

        $this->assertSame('1.1', $this->response->getProtocolVersion());
    }

    public function testGetReasonPhrase(): void
    {
        $this->psrResponse->allows('getReasonPhrase')->andReturns('Bad Request');

        $this->assertSame('Bad Request', $this->response->getReasonPhrase());
    }

    public function testGetStatusCode(): void
    {
        $this->psrResponse->allows('getStatusCode')->andReturns(403);

        $this->assertSame(403, $this->response->getStatusCode());
        $this->assertSame(403, $this->response->statusCode());
    }

    public function testGetStatusLine(): void
    {
        $this->psrResponse->allows('getProtocolVersion')->andReturns('1.2');
        $this->psrResponse->allows('getReasonPhrase')->andReturns("I'm a teapot");
        $this->psrResponse->allows('getStatusCode')->andReturns(418);

        $this->assertSame("HTTP/1.2 418 I'm a teapot", $this->response->getStatusLine());
        $this->assertSame("HTTP/1.2 418 I'm a teapot", $this->response->statusLine());
    }

    public function testTime(): void
    {
        $this->markTestIncomplete('Need to implement ' . ResponseImpl::class . '::time() and getTime()');
    }

    public function testHasHeader(): void
    {
        $this->psrResponse->allows('hasHeader')->with('my-header')->andReturns(true);

        $this->assertTrue($this->response->hasHeader('my-header'));
    }

    public function testPath(): void
    {
        $this->markTestIncomplete('Need to implement ' . ResponseImpl::class . '::path()');
    }

    public function testPrettyPrint(): void
    {
        $this->markTestIncomplete('Need to implement ' . ResponseImpl::class . '::prettyPrint()');
    }

    public function testPrint(): void
    {
        $this->stream->expects('rewind')->once();
        $this->stream->expects('getContents')->once()->andReturns('Hello, Goodbye!');

        $this->expectOutputString('Hello, Goodbye!');

        // It returns as well as echoes the contents of the stream.
        $this->assertSame('Hello, Goodbye!', $this->response->print());
    }

    public function testThen(): void
    {
        $this->assertInstanceOf(ValidatableResponseOptionsImpl::class, $this->response->then());
    }

    public function testThenReturn(): void
    {
        $this->assertSame($this->response, $this->response->thenReturn());
    }

    /**
     * @param mixed[] $args
     */
    #[DataProvider('notImplementedMethodsProvider')]
    public function testNotImplementedMethods(string $method, array $args): void
    {
        $this->expectException(NotImplemented::class);
        $this->expectExceptionMessage(
            "The $method() method is not implemented, since it does not "
            . 'make sense to manipulate the response in the context of REST Certain',
        );

        $this->response->{$method}(...$args);
    }

    /**
     * @return array<array{method: string, args: mixed[]}>
     */
    public static function notImplementedMethodsProvider(): array
    {
        return [
            ['method' => 'withAddedHeader', 'args' => ['foo', 'bar']],
            ['method' => 'withBody', 'args' => [Mockery::mock(StreamInterface::class)]],
            ['method' => 'withHeader', 'args' => ['foo', 'bar']],
            ['method' => 'withProtocolVersion', 'args' => ['1.2']],
            ['method' => 'withStatus', 'args' => [404, 'Not Found']],
            ['method' => 'withoutHeader', 'args' => ['foo']],
        ];
    }
}
