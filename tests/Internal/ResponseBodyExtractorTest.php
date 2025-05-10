<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Internal\HttpResponseBody;
use RestCertain\Internal\ResponseBodyExtractor;

use function json_decode;

class ResponseBodyExtractorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private HttpResponseBody $httpResponseBody;
    private ResponseBodyExtractor $responseBodyExtractor;

    protected function setUp(): void
    {
        $stream = Mockery::spy(StreamInterface::class, [
            'getContents' => 'Hello, World!',
        ]);

        $this->httpResponseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $this->responseBodyExtractor = new ResponseBodyExtractor($this->httpResponseBody);
    }

    public function testAsPrettyString(): void
    {
        $this->assertSame('Hello, World!', $this->responseBodyExtractor->asPrettyString());
    }

    public function testAsPrettyStringJson(): void
    {
        $stream = Mockery::spy(StreamInterface::class, [
            'getContents' => '{"foo": {"bar": "baz"}}',
        ]);

        $httpResponseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $responseBodyExtractor = new ResponseBodyExtractor($httpResponseBody);

        $expected = <<<'JSON'
            {
                "foo": {
                    "bar": "baz"
                }
            }
            JSON;

        $this->assertSame($expected, $responseBodyExtractor->asPrettyString());
    }

    public function testAsPsrStream(): void
    {
        $this->assertSame($this->httpResponseBody, $this->responseBodyExtractor->asPsrStream());
    }

    public function testAsString(): void
    {
        $this->assertSame('Hello, World!', $this->responseBodyExtractor->asString());
    }

    public function testPathUsingJmesPath(): void
    {
        $value = '{"foo": {"bar": [{"id": 123}, {"id": 456}]}}';

        $stream = Mockery::spy(StreamInterface::class, [
            'getContents' => $value,
        ]);

        $httpResponseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $responseBodyExtractor = new ResponseBodyExtractor($httpResponseBody);

        $this->assertSame([123, 456], $responseBodyExtractor->path('foo.bar[*].id'));
        $this->assertSame(456, $responseBodyExtractor->path('foo.bar[1].id'));
        $this->assertEquals(json_decode($value), $responseBodyExtractor->path('@'));
    }
}
