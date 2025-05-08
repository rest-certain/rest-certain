<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Config;
use RestCertain\Internal\HttpResponse;
use RestCertain\Internal\HttpResponseBody;
use RestCertain\Internal\RequestBuilder;
use RestCertain\Internal\ResponseValidator;

class HttpResponseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private RequestInterface & MockInterface $psrRequest;
    private ResponseInterface & MockInterface $psrResponse;
    private HttpResponse $response;
    private StreamInterface & MockInterface $stream;

    protected function setUp(): void
    {
        $this->stream = Mockery::mock(StreamInterface::class);
        $this->psrRequest = Mockery::mock(RequestInterface::class);
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

        $this->response = new HttpResponse(
            new RequestBuilder(new Config()),
            $this->psrResponse,
            $this->psrRequest,
        );
    }

    public function testAndReturn(): void
    {
        $this->assertSame($this->response, $this->response->andReturn());
    }

    public function testAsPrettyString(): void
    {
        // The SUT calls these methods twice, but we should figure out a way to
        // ensure they're only called once during the asPrettyString() code path.
        $this->stream->expects('rewind')->twice();
        $this->stream->expects('getContents')->twice()->andReturns('Hello, World!');

        $this->assertSame('Hello, World!', $this->response->asPrettyString());
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

        $this->assertInstanceOf(HttpResponseBody::class, $body);
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

    public function testGetPsrRequest(): void
    {
        $this->assertSame($this->psrRequest, $this->response->getPsrRequest());
    }

    public function testGetPsrResponse(): void
    {
        $this->assertSame($this->psrResponse, $this->response->getPsrResponse());
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

    public function testHasHeader(): void
    {
        $this->psrResponse->allows('hasHeader')->with('my-header')->andReturns(true);

        $this->assertTrue($this->response->hasHeader('my-header'));
    }

    public function testPath(): void
    {
        $this->stream->expects('rewind')->once();
        $this->stream->expects('getContents')->once()->andReturns('{"foo": {"bar": "baz"}}');

        $this->assertSame('baz', $this->response->path('foo.bar'));
    }

    public function testPrettyPrint(): void
    {
        // The SUT calls these methods twice, but we should figure out a way to
        // ensure they're only called once during the prettyPrint() code path.
        $this->stream->expects('rewind')->twice();
        $this->stream->expects('getContents')->twice()->andReturns('Hello, Goodbye!');

        $this->expectOutputString('Hello, Goodbye!');

        // It returns as well as echoes the contents of the stream.
        $this->assertSame('Hello, Goodbye!', $this->response->prettyPrint());
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
        $this->assertInstanceOf(ResponseValidator::class, $this->response->then());
    }

    public function testThenReturn(): void
    {
        $this->assertSame($this->response, $this->response->thenReturn());
    }

    public function testTime(): void
    {
        $this->markTestIncomplete('Need to implement ' . HttpResponse::class . '::time() and getTime()');
    }

    public function testWithAddedHeader(): void
    {
        $this->psrResponse->expects('withAddedHeader')->with('foo', 'bar')->andReturns(clone $this->psrResponse);

        $this->assertNotSame($this->response, $this->response->withAddedHeader('foo', 'bar'));
    }

    public function testWithBody(): void
    {
        $body = Mockery::mock(StreamInterface::class);
        $this->psrResponse->expects('withBody')->with($body)->andReturns(clone $this->psrResponse);

        $this->assertNotSame($this->response, $this->response->withBody($body));
    }

    public function testWithHeader(): void
    {
        $this->psrResponse->expects('withHeader')->with('foo', 'bar')->andReturns(clone $this->psrResponse);

        $this->assertNotSame($this->response, $this->response->withHeader('foo', 'bar'));
    }

    public function testWithProtocolVersion(): void
    {
        $this->psrResponse->expects('withProtocolVersion')->with('1.1')->andReturns(clone $this->psrResponse);

        $this->assertNotSame($this->response, $this->response->withProtocolVersion('1.1'));
    }

    public function testWithStatus(): void
    {
        $this->psrResponse->expects('withStatus')->with(303, '')->andReturns(clone $this->psrResponse);

        $this->assertNotSame($this->response, $this->response->withStatus(303));
    }

    public function testWithoutHeader(): void
    {
        $this->psrResponse->expects('withoutHeader')->with('foo')->andReturns(clone $this->psrResponse);

        $this->assertNotSame($this->response, $this->response->withoutHeader('foo'));
    }
}
