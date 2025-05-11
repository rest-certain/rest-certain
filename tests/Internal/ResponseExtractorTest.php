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
use RestCertain\Internal\RequestBuilder;
use RestCertain\Internal\ResponseBodyExtractor;
use RestCertain\Internal\ResponseExtractor;

class ResponseExtractorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private RequestInterface & MockInterface $psrRequest;
    private ResponseInterface & MockInterface $psrResponse;
    private HttpResponse $response;
    private ResponseExtractor $responseExtractor;
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

        $this->responseExtractor = new ResponseExtractor($this->response);
    }

    public function testAsPrettyString(): void
    {
        // The SUT calls this method twice, but we should figure out a way to
        // ensure they're only called once during the asPrettyString() code path.
        $this->stream->expects('__toString')->twice()->andReturns('Hello, World!');

        $this->assertSame('Hello, World!', $this->responseExtractor->asPrettyString());
    }

    public function testAsPsrStream(): void
    {
        $this->assertSame($this->response->body(), $this->responseExtractor->asPsrStream());
    }

    public function testAsString(): void
    {
        $this->stream->expects('__toString')->once()->andReturns('Hello, World!');

        $this->assertSame('Hello, World!', $this->responseExtractor->asString());
    }

    public function testBody(): void
    {
        $this->assertInstanceOf(ResponseBodyExtractor::class, $this->responseExtractor->body());
    }

    public function testContentType(): void
    {
        $this->psrResponse->allows('hasHeader')->with('content-type')->andReturns(true);
        $this->psrResponse->allows('getHeaderLine')->with('content-type')->andReturns('text/html');

        $this->assertSame('text/html', $this->responseExtractor->contentType());
    }

    public function testContentTypeReturnsNull(): void
    {
        $this->psrResponse->allows('hasHeader')->with('content-type')->andReturns(false);

        $this->assertNull($this->responseExtractor->contentType());
    }

    public function testCookie(): void
    {
        $this->assertSame('e8bb43229de9', $this->responseExtractor->cookie('sessionId'));
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

        $this->assertSame($expected, $this->responseExtractor->cookies());
    }

    public function testHeader(): void
    {
        $expected = [
            '<https://api.example.com/resource?page=1>; rel="self"',
            '<https://api.example.com/resource?page=2>; rel="next"',
            '<https://api.example.com/resource?page=5>; rel="last"',
        ];

        $this->psrResponse->allows('getHeader')->with('link')->andReturns($expected);

        $this->assertSame($expected, $this->responseExtractor->header('link'));
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

        $this->assertSame($expected, $this->responseExtractor->headers());
    }

    public function testPath(): void
    {
        $this->stream->expects('__toString')->once()->andReturns('{"foo": {"bar": "baz"}}');

        $this->assertSame('baz', $this->responseExtractor->path('foo.bar'));
    }

    public function testResponse(): void
    {
        $this->assertSame($this->response, $this->responseExtractor->response());
    }

    public function testStatusCode(): void
    {
        $this->psrResponse->allows('getStatusCode')->andReturns(403);

        $this->assertSame(403, $this->responseExtractor->statusCode());
    }

    public function testStatusLine(): void
    {
        $this->psrResponse->allows('getProtocolVersion')->andReturns('1.2');
        $this->psrResponse->allows('getReasonPhrase')->andReturns("I'm a teapot");
        $this->psrResponse->allows('getStatusCode')->andReturns(418);

        $this->assertSame("HTTP/1.2 418 I'm a teapot", $this->responseExtractor->statusLine());
    }

    public function testTime(): void
    {
        $this->assertSame(-1, $this->responseExtractor->time());
    }

    public function testTimeWithPassedTime(): void
    {
        $response = new HttpResponse(
            new RequestBuilder(new Config()),
            $this->psrResponse,
            $this->psrRequest,
            654,
        );

        $responseExtractor = new ResponseExtractor($response);

        $this->assertSame(654, $responseExtractor->time());
    }
}
