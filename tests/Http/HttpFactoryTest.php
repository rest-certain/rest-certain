<?php

declare(strict_types=1);

namespace RestCertain\Test\Http;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use RestCertain\Http\HttpFactory;

use function file_put_contents;
use function fwrite;
use function sys_get_temp_dir;
use function tempnam;
use function tmpfile;
use function unlink;

use const UPLOAD_ERR_OK;

class HttpFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCreateRequest(): void
    {
        $factory = new HttpFactory();
        $request = $factory->createRequest('GET', 'https://example.com');

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://example.com', (string) $request->getUri());
    }

    public function testCreateResponse(): void
    {
        $factory = new HttpFactory();
        $response = $factory->createResponse();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testCreateServerRequest(): void
    {
        $factory = new HttpFactory();
        $request = $factory->createServerRequest('GET', 'https://example.com', ['foo' => 'bar']);

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://example.com', (string) $request->getUri());
        $this->assertSame(['foo' => 'bar'], $request->getServerParams());
    }

    public function testCreateStream(): void
    {
        $factory = new HttpFactory();
        $stream = $factory->createStream('foo');

        $this->assertSame('foo', (string) $stream);
    }

    public function testCreateStreamFromFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'rest-certain');
        file_put_contents($file, 'foo');

        $factory = new HttpFactory();
        $stream = $factory->createStreamFromFile($file, 'r');

        $this->assertSame('foo', (string) $stream);

        unlink($file);
    }

    public function testCreateStreamFromResource(): void
    {
        $file = tmpfile();
        fwrite($file, 'foo');

        $factory = new HttpFactory();
        $stream = $factory->createStreamFromResource($file);

        $this->assertSame('foo', (string) $stream);
    }

    public function testCreateUploadedFile(): void
    {
        $file = tmpfile();
        fwrite($file, "foo\n");

        $factory = new HttpFactory();
        $stream = $factory->createStreamFromResource($file);
        $uploadedFile = $factory->createUploadedFile($stream, 4, UPLOAD_ERR_OK, 'foo.txt', 'text/plain');

        $this->assertSame("foo\n", (string) $uploadedFile->getStream());
        $this->assertSame(4, $uploadedFile->getSize());
        $this->assertSame('foo.txt', $uploadedFile->getClientFilename());
        $this->assertSame('text/plain', $uploadedFile->getClientMediaType());
        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
    }

    public function testCreateUri(): void
    {
        $factory = new HttpFactory();
        $uri = $factory->createUri('https://example.com');

        $this->assertSame('https://example.com', (string) $uri);
    }

    public function testSendRequest(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $client = Mockery::mock(ClientInterface::class);

        $factory = new HttpFactory($client);
        $request = $factory->createRequest('GET', 'https://example.com');

        $client->shouldReceive('sendRequest')->with($request)->andReturn($response);

        $this->assertSame($response, $factory->sendRequest($request));
    }
}
