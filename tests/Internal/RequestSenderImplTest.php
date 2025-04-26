<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use RestCertain\Internal\RequestSenderImpl;
use RestCertain\Response\Response;
use RestCertain\Specification\RequestSpecification;
use RestCertain\Specification\ResponseSpecification;
use RestCertain\Test\Str;
use Stringable;

class RequestSenderImplTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Response $response;
    private RequestSpecification & MockInterface $requestSpecification;
    private RequestSenderImpl $requestSender;

    protected function setUp(): void
    {
        $responseSpecification = Mockery::mock(ResponseSpecification::class);
        $this->requestSpecification = Mockery::mock(RequestSpecification::class);

        $responseSpecification
            ->allows('setRequestSpecification')
            ->with($this->requestSpecification)
            ->andReturnSelf();

        $this->requestSpecification
            ->allows('setResponseSpecification')
            ->with($responseSpecification)
            ->andReturnSelf();

        $this->response = Mockery::mock(Response::class);

        $this->requestSender = new RequestSenderImpl($this->requestSpecification, $responseSpecification);
    }

    /**
     * @param array<string, Stringable | int | string> $pathParams
     */
    #[DataProvider('requestSenderMethodsProvider')]
    public function testRequestSenderMethods(
        string $method,
        Stringable | UriInterface | string $path,
        array $pathParams = [],
    ): void {
        $this->requestSpecification
            ->expects($method)
            ->with($path, $pathParams)
            ->andReturns($this->response);

        $this->requestSpecification
            ->expects('request')
            ->with($method, $path, $pathParams)
            ->andReturns($this->response);

        $this->assertSame($this->response, $this->requestSender->{$method}($path, $pathParams));
        $this->assertSame($this->response, $this->requestSender->request($method, $path, $pathParams));
    }

    /**
     * @return array<array{
     *     method: Stringable | string,
     *     path: Stringable | UriInterface | string,
     *     pathParams: array<string, Stringable | int | string>,
     * }>
     */
    public static function requestSenderMethodsProvider(): array
    {
        $path = '/foo';
        $pathStringable = new Str('/foo');
        $pathUri = new Uri('https://example.com/foo');

        $pathParams = [
            'foo' => 'bar',
            'baz' => new Str('qux'),
            'quux' => 123,
        ];

        return [
            ['method' => 'delete', 'path' => $path, 'pathParams' => $pathParams],
            ['method' => 'delete', 'path' => $pathStringable, 'pathParams' => $pathParams],
            ['method' => 'delete', 'path' => $pathUri, 'pathParams' => $pathParams],
            ['method' => 'get', 'path' => $path, 'pathParams' => $pathParams],
            ['method' => 'get', 'path' => $pathStringable, 'pathParams' => $pathParams],
            ['method' => 'get', 'path' => $pathUri, 'pathParams' => $pathParams],
            ['method' => 'head', 'path' => $path, 'pathParams' => $pathParams],
            ['method' => 'head', 'path' => $pathStringable, 'pathParams' => $pathParams],
            ['method' => 'head', 'path' => $pathUri, 'pathParams' => $pathParams],
            ['method' => 'options', 'path' => $path, 'pathParams' => $pathParams],
            ['method' => 'options', 'path' => $pathStringable, 'pathParams' => $pathParams],
            ['method' => 'options', 'path' => $pathUri, 'pathParams' => $pathParams],
            ['method' => 'patch', 'path' => $path, 'pathParams' => $pathParams],
            ['method' => 'patch', 'path' => $pathStringable, 'pathParams' => $pathParams],
            ['method' => 'patch', 'path' => $pathUri, 'pathParams' => $pathParams],
            ['method' => 'post', 'path' => $path, 'pathParams' => $pathParams],
            ['method' => 'post', 'path' => $pathStringable, 'pathParams' => $pathParams],
            ['method' => 'post', 'path' => $pathUri, 'pathParams' => $pathParams],
            ['method' => 'put', 'path' => $path, 'pathParams' => $pathParams],
            ['method' => 'put', 'path' => $pathStringable, 'pathParams' => $pathParams],
            ['method' => 'put', 'path' => $pathUri, 'pathParams' => $pathParams],
        ];
    }
}
