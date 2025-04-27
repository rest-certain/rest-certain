<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Internal\HttpResponseBody;

use const SEEK_SET;

class HttpResponseBodyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private HttpResponseBody $responseBody;
    private StreamInterface & MockInterface $stream;

    protected function setUp(): void
    {
        $this->stream = Mockery::spy(StreamInterface::class, [
            'getContents' => 'Hello, World!',
        ]);
        $this->responseBody = new HttpResponseBody(Mockery::mock(ResponseInterface::class, [
            'getBody' => $this->stream,
        ]));
    }

    public function testToString(): void
    {
        $this->assertSame('Hello, World!', (string) $this->responseBody);

        $this->stream->shouldHaveReceived('rewind');
    }

    public function testAsPrettyString(): void
    {
        $this->markTestIncomplete('Need to implement ' . HttpResponseBody::class . '::asPrettyString()');
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

    public function testPath(): void
    {
        $this->markTestIncomplete('Need to implement ' . HttpResponseBody::class . '::path()');
    }

    public function testPrettyPrint(): void
    {
        $this->markTestIncomplete('Need to implement ' . HttpResponseBody::class . '::prettyPrint()');
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
