<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use JsonSerializable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Config;
use RestCertain\Exception\PendingRequest;
use RestCertain\Exception\RequestFailed;
use RestCertain\Exception\TooManyBodies;
use RestCertain\Http\Header;
use RestCertain\Http\Method;
use RestCertain\Internal\HttpResponse;
use RestCertain\Internal\RequestBuilder;
use RestCertain\Internal\ResponseExpectations;
use RestCertain\Request\Sender;
use RestCertain\RestCertain;
use RestCertain\Specification\ResponseSpecification;
use RestCertain\Test\Json;
use RestCertain\Test\Str;
use RuntimeException;
use SplFileInfo;
use SplFileObject;
use SplTempFileObject;
use Stringable;
use stdClass;

use function basename;
use function strtoupper;
use function sys_get_temp_dir;
use function tempnam;

class RequestBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Psr17Factory $factory;
    private RequestBuilder $spec;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
        $this->spec = new RequestBuilder(new Config());
    }

    public function testAccept(): void
    {
        $this->assertSame($this->spec, $this->spec->accept('application/json'));
        $this->assertSame($this->spec, $this->spec->accept(new Str('application/xml')));
    }

    public function testAnd(): void
    {
        $this->assertSame($this->spec, $this->spec->and());
    }

    public function testBasePath(): void
    {
        $this->assertSame($this->spec, $this->spec->basePath('/foo'));
        $this->assertSame($this->spec, $this->spec->basePath(new Str('/bar')));
    }

    public function testBaseUri(): void
    {
        $this->assertSame($this->spec, $this->spec->baseUri('https://api.example.net/foo'));
        $this->assertSame($this->spec, $this->spec->baseUri($this->factory->createUri('https://api.example.net/bar')));
        $this->assertSame($this->spec, $this->spec->baseUri(new Str('https://api.example.net/baz')));
    }

    public function testBody(): void
    {
        $this->assertSame($this->spec, $this->spec->body('{"foo": "bar"}'));
        $this->assertSame($this->spec, $this->spec->body(new Str('{"foo": "bar"}')));
        $this->assertSame($this->spec, $this->spec->body(new Json(['foo' => 'bar'])));
        $this->assertSame($this->spec, $this->spec->body($this->factory->createStream('{"foo": "bar"}')));

        $file = new SplTempFileObject();
        $file->fwrite('{"foo": "bar"}');
        $this->assertSame($this->spec, $this->spec->body($file));
    }

    public function testContentType(): void
    {
        $this->assertSame($this->spec, $this->spec->contentType('application/json'));
        $this->assertSame($this->spec, $this->spec->contentType(new Str('application/xml')));
    }

    public function testCookie(): void
    {
        $this->assertSame($this->spec, $this->spec->cookie('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->cookie('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->cookie('quux', ''));
        $this->assertSame($this->spec, $this->spec->cookie('corge'));
    }

    public function testCookies(): void
    {
        $this->assertSame($this->spec, $this->spec->cookies([
            'foo' => 'bar',
            'baz' => new Str('qux'),
            'quux' => '',
            'corge' => null,
        ]));
    }

    #[DataProvider('requestMethodProvider')]
    public function testRequestMethod(string $method): void
    {
        $psrResponse = $this->factory->createResponse(200);
        $config = new Config(httpClient: Mockery::mock(ClientInterface::class, ['sendRequest' => $psrResponse]));
        $spec = new RequestBuilder($config);

        $spec->params([
            'foo' => 'bar',
            'baz' => new Str('qux'),
        ]);

        // POST handles params differently.
        if ($method === 'post') {
            $expectedRequestUrl = 'http://localhost:8000/user/123/foo';
            $expectedRequestBody = 'foo=bar&baz=qux';
        } else {
            $expectedRequestUrl = 'http://localhost:8000/user/123/foo?foo=bar&baz=qux';
            $expectedRequestBody = '';
        }

        $response = $spec->{$method}(
            '/{entity}/{id}/{subId}',
            [
                'entity' => 'user',
                'id' => 123,
                'subId' => new Str('foo'),
            ],
        );

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertSame(strtoupper($method), (string) $response->getPsrRequest()->getMethod());
        $this->assertSame($expectedRequestUrl, (string) $response->getPsrRequest()->getUri());
        $this->assertSame($expectedRequestBody, (string) $response->getPsrRequest()->getBody());
        $this->assertTrue($response->getPsrRequest()->hasHeader(Header::USER_AGENT));
        $this->assertSame(RestCertain::USER_AGENT, $response->getPsrRequest()->getHeaderLine(Header::USER_AGENT));
    }

    /**
     * @return array<array{method: string}>
     */
    public static function requestMethodProvider(): array
    {
        return [
            ['method' => 'delete'],
            ['method' => 'get'],
            ['method' => 'head'],
            ['method' => 'options'],
            ['method' => 'patch'],
            ['method' => 'post'],
            ['method' => 'put'],
        ];
    }

    public function testExpect(): void
    {
        $responseSpecification = Mockery::mock(ResponseSpecification::class);
        $this->spec->setResponseSpecification($responseSpecification);

        $this->assertSame($responseSpecification, $this->spec->expect());
    }

    public function testExpectThrowsExceptionIfResponseSpecificationNotSet(): void
    {
        $this->expectException(PendingRequest::class);
        $this->expectExceptionMessage(
            'Cannot call expect() before sending a request or setting a response '
            . 'specification with setResponseSpecification(); to send a request, call any of the'
            . Sender::class . ' methods',
        );

        $this->spec->expect();
    }

    public function testFormParam(): void
    {
        $this->assertSame($this->spec, $this->spec->formParam('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->formParam('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->formParam('quux', 'corge', new Str('grault')));
        $this->assertSame($this->spec, $this->spec->formParam('grault', 'garply', 'waldo', new Str('fred')));
        $this->assertSame($this->spec, $this->spec->formParam('foo', 'plugh', 'xyzzy'));
        $this->assertSame($this->spec, $this->spec->formParam('thud', ''));
    }

    public function testFormParams(): void
    {
        $this->assertSame($this->spec, $this->spec->formParams([
            'foo' => ['bar', new Str('baz')],
            'qux' => new Str('quux'),
            'corge' => 'grault',
            'garply' => '',
        ]));
    }

    public function testGiven(): void
    {
        $this->assertSame($this->spec, $this->spec->given());
    }

    public function testHeader(): void
    {
        $this->assertSame($this->spec, $this->spec->header('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->header('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->header('quux', 'corge', new Str('grault'), 'garply'));
        $this->assertSame($this->spec, $this->spec->header('foo', 'waldo'));
        $this->assertSame($this->spec, $this->spec->header('Cookie', 'fred', new Str('plugh')));
    }

    public function testHeaders(): void
    {
        $this->assertSame($this->spec, $this->spec->headers([
            'foo' => 'bar',
            'baz' => new Str('qux'),
            'quux' => ['corge', new Str('grault'), 'garply'],
            'cookie' => ['waldo', new Str('fred')],
        ]));
    }

    public function testParam(): void
    {
        $this->assertSame($this->spec, $this->spec->param('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->param('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->param('quux', 'corge', new Str('grault')));
        $this->assertSame($this->spec, $this->spec->param('grault', 'garply', 'waldo', new Str('fred')));
        $this->assertSame($this->spec, $this->spec->param('foo', 'plugh', 'xyzzy'));
        $this->assertSame($this->spec, $this->spec->param('thud', ''));
    }

    public function testParams(): void
    {
        $this->assertSame($this->spec, $this->spec->params([
            'foo' => ['bar', new Str('baz')],
            'qux' => new Str('quux'),
            'corge' => 'grault',
            'garply' => '',
        ]));
    }

    public function testPathParam(): void
    {
        $this->assertSame($this->spec, $this->spec->pathParam('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->pathParam('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->pathParam('quux', 123));
    }

    public function testPathParams(): void
    {
        $this->assertSame($this->spec, $this->spec->pathParams([
            'foo' => 'bar',
            'baz' => new Str('qux'),
            'quux' => 123,
        ]));
    }

    public function testPort(): void
    {
        $this->assertSame($this->spec, $this->spec->port(8080));
    }

    public function testQueryParam(): void
    {
        $this->assertSame($this->spec, $this->spec->queryParam('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->queryParam('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->queryParam('quux', 'corge', new Str('grault')));
        $this->assertSame($this->spec, $this->spec->queryParam('grault', 'garply', 'waldo', new Str('fred')));
        $this->assertSame($this->spec, $this->spec->queryParam('foo', 'plugh', 'xyzzy'));
        $this->assertSame($this->spec, $this->spec->queryParam('thud', ''));
    }

    public function testQueryParams(): void
    {
        $this->assertSame($this->spec, $this->spec->queryParams([
            'foo' => ['bar', new Str('baz')],
            'qux' => new Str('quux'),
            'corge' => 'grault',
            'garply' => '',
        ]));
    }

    public function testRequest(): void
    {
        $psrResponse = $this->factory->createResponse(200);
        $config = new Config(httpClient: Mockery::mock(ClientInterface::class, ['sendRequest' => $psrResponse]));
        $spec = new RequestBuilder($config);

        $response = $spec->request(
            Method::GET,
            '/{entity}/{id}/{subId}',
            [
                'entity' => 'user',
                'id' => 123,
                'subId' => new Str('foo'),
            ],
        );

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertSame('http://localhost:8000/user/123/foo', (string) $response->getPsrRequest()->getUri());
        $this->assertSame('GET', (string) $response->getPsrRequest()->getMethod());
    }

    public function testResponse(): void
    {
        $responseSpecification = Mockery::mock(ResponseSpecification::class);
        $this->spec->setResponseSpecification($responseSpecification);

        $this->assertSame($responseSpecification, $this->spec->response());
    }

    public function testResponseThrowsExceptionIfResponseSpecificationNotSet(): void
    {
        $this->expectException(PendingRequest::class);
        $this->expectExceptionMessage(
            'Cannot call response() before sending a request or setting a response '
            . 'specification with setResponseSpecification(); to send a request, call any of the'
            . Sender::class . ' methods',
        );

        $this->spec->response();
    }

    public function testSetResponseSpecification(): void
    {
        $responseSpecification = Mockery::mock(ResponseSpecification::class);

        $this->assertSame($this->spec, $this->spec->setResponseSpecification($responseSpecification));
    }

    public function testThat(): void
    {
        $this->assertSame($this->spec, $this->spec->that());
    }

    public function testThen(): void
    {
        $responseSpecification = Mockery::mock(ResponseSpecification::class);
        $this->spec->setResponseSpecification($responseSpecification);

        $this->assertSame($responseSpecification, $this->spec->then());
    }

    public function testThenThrowsExceptionIfResponseSpecificationNotSet(): void
    {
        $this->expectException(PendingRequest::class);
        $this->expectExceptionMessage(
            'Cannot call then() before sending a request or setting a response '
            . 'specification with setResponseSpecification(); to send a request, call any of the'
            . Sender::class . ' methods',
        );

        $this->spec->then();
    }

    public function testWhen(): void
    {
        $this->assertSame($this->spec, $this->spec->when());
    }

    public function testWith(): void
    {
        $this->assertSame($this->spec, $this->spec->with());
    }

    public function testApplyPathParamsAndSendRequestWithoutRequestBody(): void
    {
        $psrResponse = $this->factory
            ->createResponse(200)
            ->withBody($this->factory->createStream('{"foo": "bar"}'))
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Set-Cookie', 'sessionId=0987654321')
            ->withAddedHeader('set-cookie', 'my-cookie="my cookie value"');

        $httpClient = Mockery::mock(ClientInterface::class, [
            'sendRequest' => $psrResponse,
        ]);

        $config = new Config(httpClient: $httpClient);
        $spec = new RequestBuilder($config);

        $response = $spec
            ->given()
            ->pathParam('entity', 'user')
            ->param('param1', 'a', new Str('b'), 'c')
            ->param('param2', new Str('d'))
            ->queryParam('param2', new Str('e'))
            ->queryParam('param3', 'f')
            ->queryParam('param3', 'g', new Str('h'))
            ->queryParams([
                'param3' => ['i', new Str('j')],
                'cheese' => ['Sakura cheese', new Str('feta'), 'höfðingi'],
                'crackers' => 'yes',
            ])
            ->cookie('sessionId', new Str('1234567890'))
            ->cookie('mmm')
            ->cookies([
                'abc' => '765',
                'def' => new Str('890'),
                'ghi' => null,
            ])
            ->header('X-Data', 'foo bar baz')
            ->accept('application/json')
            ->when()
            ->get('/{entity}/{id}/{subId}', ['id' => 123, 'subId' => new Str('foo')]);

        $response
            ->then()
            ->statusCode(200)
            ->contentType('application/json')
            ->cookie('sessionId', '0987654321', $this->stringContains('765'))
            ->cookie('my-cookie')
            ->body('{"foo": "bar"}');

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertSame(
            'http://localhost:8000/user/123/foo?param1=a&param1=b&param1=c&param2=e&param3=f&param3=g&param3=h&param3=i'
            . '&param3=j&cheese=Sakura%20cheese&cheese=feta&cheese=h%C3%B6f%C3%B0ingi&crackers=yes',
            (string) $response->getPsrRequest()->getUri(),
        );
        $this->assertSame(
            'sessionId=1234567890; mmm=; abc=765; def=890; ghi=',
            $response->getPsrRequest()->getHeaderLine('cookie'),
        );
        $this->assertSame('foo bar baz', $response->getPsrRequest()->getHeaderLine('x-data'));
        $this->assertSame('application/json', $response->getPsrRequest()->getHeaderLine('accept'));
        $this->assertSame('', (string) $response->getPsrRequest()->getBody());

        // After sending the request, we should have a ResponseSpecification that we can get by calling expect().
        $this->assertInstanceOf(ResponseExpectations::class, $spec->expect());
    }

    public function testApplyPathParamsAndSendRequestWithFormUrlencodedBody(): void
    {
        $psrResponse = $this->factory
            ->createResponse(200)
            ->withBody($this->factory->createStream('{"foo": "bar"}'));

        $httpClient = Mockery::mock(ClientInterface::class, [
            'sendRequest' => $psrResponse,
        ]);

        $config = new Config(httpClient: $httpClient);
        $spec = new RequestBuilder($config);

        $response = $spec
            ->given()
            ->param('param1', 'a', new Str('b'), 'c')
            ->param('param2', new Str('d'))
            ->param('param3', 'e')
            ->queryParam('qs1', 'abc')
            ->formParam('param3', 'f')
            ->formParam('param4', 'g', 'h', new Str('i'))
            ->formParam('param4', 'j')
            ->formParam('param5', new Str('k'))
            ->formParams([
                'param4' => ['l', new Str('m')],
                'cheese' => ['Sakura cheese', new Str('feta'), 'höfðingi'],
                'crackers' => 'yes',
            ])
            ->when()
            ->post('/foo');

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertSame('http://localhost:8000/foo?qs1=abc', (string) $response->getPsrRequest()->getUri());
        $this->assertSame(
            'param1=a&param1=b&param1=c&param2=d&param3=f&param4=g&param4=h&param4=i&param4=j&param4=l&param4=m'
            . '&param5=k&cheese=Sakura+cheese&cheese=feta&cheese=h%C3%B6f%C3%B0ingi&crackers=yes',
            (string) $response->getPsrRequest()->getBody(),
        );
    }

    /**
     * @param JsonSerializable | SplFileInfo | StreamInterface | Stringable | stdClass | mixed[] | string $body
     */
    #[DataProvider('bodyProvider')]
    public function testApplyPathParamsAndSendRequestWithBody(
        JsonSerializable | SplFileInfo | StreamInterface | Stringable | stdClass | array | string $body,
        string $expectedBody,
        string $expectedContentType,
    ): void {
        $psrResponse = $this->factory
            ->createResponse(200)
            ->withBody($this->factory->createStream('{"foo": "bar"}'));

        $httpClient = Mockery::mock(ClientInterface::class, [
            'sendRequest' => $psrResponse,
        ]);

        $config = new Config(httpClient: $httpClient);
        $spec = new RequestBuilder($config);

        $response = $spec
            ->given()
            ->body($body)
            ->when()
            ->post('/foo');

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertSame($expectedContentType, $response->getPsrRequest()->getHeaderLine('content-type'));
        $this->assertSame($expectedBody, (string) $response->getPsrRequest()->getBody());
    }

    /**
     * @return array<array{
     *     body: JsonSerializable | SplFileInfo | StreamInterface | Stringable | stdClass | mixed[] | string,
     *     expectedBody: string,
     *     expectedContentType: string,
     * }>
     */
    public static function bodyProvider(): array
    {
        return [
            [
                'body' => new Json(['foo' => 'bar']),
                'expectedBody' => '{"foo":"bar"}',
                'expectedContentType' => 'application/json',
            ],
            [
                'body' => ['url' => 'https://example.com/bar', 'feelings' => '😁'],
                'expectedBody' => '{"url":"https://example.com/bar","feelings":"😁"}',
                'expectedContentType' => 'application/json',
            ],
            [
                'body' => (object) ['baz' => 'qux'],
                'expectedBody' => '{"baz":"qux"}',
                'expectedContentType' => 'application/json',
            ],
            [
                'body' => (function (): SplFileInfo {
                    $file = new SplFileObject(tempnam(sys_get_temp_dir(), basename(__FILE__, '.php')), 'w');
                    $file->fwrite("this is a body created from a file\n");
                    $file->fwrite("this is another line in that body\n");

                    return $file;
                })(),
                'expectedBody' => "this is a body created from a file\nthis is another line in that body\n",
                'expectedContentType' => 'application/octet-stream',
            ],
            [
                'body' => (new Psr17Factory())->createStream('this is a standard string body'),
                'expectedBody' => 'this is a standard string body',
                'expectedContentType' => 'application/octet-stream',
            ],
            [
                'body' => 'this is a standard string body',
                'expectedBody' => 'this is a standard string body',
                'expectedContentType' => 'text/plain',
            ],
        ];
    }

    public function testApplyPathParamsAndSendRequestThrowsHttpClientException(): void
    {
        $exception = new class ('Something went wrong') extends RuntimeException implements ClientExceptionInterface {
        };

        $httpClient = Mockery::mock(ClientInterface::class);
        $httpClient->expects('sendRequest')->andThrows($exception);

        $config = new Config(httpClient: $httpClient);
        $spec = new RequestBuilder($config);

        $this->expectException(RequestFailed::class);
        $this->expectExceptionMessage('The request failed: Something went wrong');

        $spec->get('/foo');
    }

    public function testApplyPathParamsAndSendRequestWithFormParametersAndBody(): void
    {
        $this->spec->formParam('param', 'abc')->body('body content');

        $this->expectException(TooManyBodies::class);
        $this->expectExceptionMessage('Cannot set both body and form data');

        $this->spec->post('/foo');
    }

    public function testApplyPathParamsAndSendRequestWithAbsoluteUrl(): void
    {
        $psrResponse = $this->factory
            ->createResponse(200)
            ->withBody($this->factory->createStream('{"foo": "bar"}'));

        $httpClient = Mockery::mock(ClientInterface::class, [
            'sendRequest' => $psrResponse,
        ]);

        $config = new Config(httpClient: $httpClient);
        $spec = new RequestBuilder($config);

        $response = $spec->get('https://api.example.com/bar');

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertSame('https://api.example.com/bar', (string) $response->getPsrRequest()->getUri());
    }

    public function testCustomUserAgent(): void
    {
        $psrResponse = $this->factory->createResponse(200);
        $config = new Config(httpClient: Mockery::mock(ClientInterface::class, ['sendRequest' => $psrResponse]));
        $spec = new RequestBuilder($config);

        $spec->header('User-Agent', 'MyUserAgent/1234');

        $response = $spec->get('/entity/1234');

        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertTrue($response->getPsrRequest()->hasHeader(Header::USER_AGENT));
        $this->assertSame('MyUserAgent/1234', $response->getPsrRequest()->getHeaderLine(Header::USER_AGENT));
    }
}
