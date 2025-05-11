<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Exception\PathResolutionFailure;
use RestCertain\Internal\HttpResponseBody;

use function json_decode;

use const SEEK_SET;

class HttpResponseBodyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private HttpResponseBody $responseBody;
    private StreamInterface & MockInterface $stream;

    protected function setUp(): void
    {
        $this->stream = Mockery::spy(StreamInterface::class, [
            '__toString' => 'Hello, World!',
            'getContents' => 'Hello, World!',
        ]);
        $this->responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $this->stream,
        ]));
    }

    public function testToString(): void
    {
        $this->assertSame('Hello, World!', (string) $this->responseBody);

        $this->stream->shouldHaveReceived('__toString');
    }

    public function testAsPrettyString(): void
    {
        $this->assertSame('Hello, World!', $this->responseBody->asPrettyString());
    }

    public function testAsPrettyStringJson(): void
    {
        $stream = Mockery::spy(StreamInterface::class, [
            '__toString' => '{"foo": {"bar": "baz"}}',
        ]);

        $responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $expected = <<<'JSON'
            {
                "foo": {
                    "bar": "baz"
                }
            }
            JSON;

        $this->assertSame($expected, $responseBody->asPrettyString());
    }

    public function testAsString(): void
    {
        $this->assertSame('Hello, World!', $this->responseBody->asString());
    }

    public function testClose(): void
    {
        $this->responseBody->close();

        $this->stream->shouldHaveReceived('close');
    }

    public function testDetach(): void
    {
        $this->responseBody->detach();

        $this->stream->shouldHaveReceived('detach');
    }

    public function testEof(): void
    {
        $this->stream->expects('eof')->once()->andReturns(false);

        $this->assertFalse($this->responseBody->eof());
    }

    public function testGetContents(): void
    {
        $this->assertSame('Hello, World!', $this->responseBody->getContents());
    }

    public function testGetMetadata(): void
    {
        $this->stream->expects('getMetadata')->with(null)->once()->andReturns(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $this->responseBody->getMetadata());
    }

    public function testGetSize(): void
    {
        $this->stream->expects('getSize')->once()->andReturns(10);

        $this->assertSame(10, $this->responseBody->getSize());
    }

    public function testIsReadable(): void
    {
        $this->stream->expects('isReadable')->once()->andReturns(true);

        $this->assertTrue($this->responseBody->isReadable());
    }

    public function testIsSeekable(): void
    {
        $this->stream->expects('isSeekable')->once()->andReturns(true);

        $this->assertTrue($this->responseBody->isSeekable());
    }

    public function testIsWritable(): void
    {
        $this->stream->expects('isWritable')->once()->andReturns(true);

        $this->assertTrue($this->responseBody->isWritable());
    }

    public function testPathUsingJmesPath(): void
    {
        $value = '{"foo": {"bar": [{"id": 123}, {"id": 456}]}}';

        $stream = Mockery::spy(StreamInterface::class, [
            '__toString' => $value,
        ]);

        $responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $this->assertSame([123, 456], $responseBody->path('foo.bar[*].id'));
        $this->assertSame(456, $responseBody->path('foo.bar[1].id'));
        $this->assertEquals(json_decode($value), $responseBody->path('@'));
    }

    public function testPathUsingJsonPath(): void
    {
        $stream = Mockery::spy(StreamInterface::class, [
            '__toString' => '{"foo": {"bar": "baz"}}',
        ]);

        $responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $this->assertSame(['baz'], $responseBody->path('$.foo.bar'));
    }

    public function testPathUsingJsonPathThrowsExceptionForSyntaxError(): void
    {
        $stream = Mockery::spy(StreamInterface::class, [
            '__toString' => '"foo bar"',
        ]);

        $responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $this->expectException(PathResolutionFailure::class);
        $this->expectExceptionMessage(
            'Unable to parse JSONPath query: Expected "*", "_", [\x80-\x{0D7FF}], [\x{0D800}-\x{0DBFF}], '
            . '[\x{0E000}-\x{0FFFF}] or [a-z] but end of input found',
        );

        $responseBody->path('$.');
    }

    public function testPathUsingJsonPathWithRootAndString(): void
    {
        $stream = Mockery::spy(StreamInterface::class, [
            '__toString' => '"foo bar"',
        ]);

        $responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $this->assertSame(['foo bar'], $responseBody->path('$'));
    }

    public function testPathUsingJmesPathThrowsExceptionWhenNotAnObject(): void
    {
        $stream = Mockery::spy(StreamInterface::class, [
            '__toString' => '"foo bar"',
        ]);

        $responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $this->expectException(PathResolutionFailure::class);
        $this->expectExceptionMessage('Unable to use a path on a JSON value that is not an object or array');

        $responseBody->path('foo.bar');
    }

    public function testPathThrowsExceptionWhenJmesPathExpressionHasASyntaxError(): void
    {
        $stream = Mockery::spy(StreamInterface::class, [
            '__toString' => '{"foo": {"bar": "baz"}}',
        ]);

        $responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $this->expectException(PathResolutionFailure::class);
        $this->expectExceptionMessage('Unable to parse JMESPath query: Syntax error at character 8');

        $responseBody->path('foo.bar..qux');
    }

    public function testPathWithInvalidJson(): void
    {
        $stream = Mockery::spy(StreamInterface::class, [
            '__toString' => 'this is not a JSON string',
        ]);

        $responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $this->expectException(PathResolutionFailure::class);
        $this->expectExceptionMessage(
            "The response body is not a valid JSON value.\nReceived:\nthis is not a JSON string",
        );

        $responseBody->path('foo.bar');
    }

    public function testPrettyPrint(): void
    {
        $this->expectOutputString('Hello, World!');

        // It returns as well as echoes the contents of the stream.
        $this->assertSame('Hello, World!', $this->responseBody->prettyPrint());
    }

    public function testPrettyPrintJson(): void
    {
        $stream = Mockery::spy(StreamInterface::class, [
            '__toString' => '{"foo": {"bar": "baz"}}',
        ]);

        $responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $stream,
        ]));

        $expected = <<<'JSON'
            {
                "foo": {
                    "bar": "baz"
                }
            }
            JSON;

        $this->expectOutputString($expected);

        // It returns as well as echoes the contents of the stream.
        $this->assertSame($expected, $responseBody->prettyPrint());
    }

    public function testPrint(): void
    {
        $this->expectOutputString('Hello, World!');

        // It returns as well as echoes the contents of the stream.
        $this->assertSame('Hello, World!', $this->responseBody->print());
    }

    public function testRead(): void
    {
        $this->stream->expects('read')->with(6)->once()->andReturns('Hello,');

        $this->assertSame('Hello,', $this->responseBody->read(6));
    }

    public function testRewind(): void
    {
        $this->responseBody->rewind();

        $this->stream->shouldHaveReceived('rewind');
    }

    public function testSeek(): void
    {
        $this->responseBody->seek(1001);

        $this->stream->shouldHaveReceived('seek', [1001, SEEK_SET]);
    }

    public function testTell(): void
    {
        $this->stream->expects('tell')->once()->andReturns(123);

        $this->assertSame(123, $this->responseBody->tell());
    }

    public function testWrite(): void
    {
        $this->stream->expects('write')->with('Hello, World!')->once()->andReturns(13);

        $this->assertSame(13, $this->responseBody->write('Hello, World!'));
    }
}
