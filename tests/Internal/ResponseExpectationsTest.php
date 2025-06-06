<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\GreaterThan;
use PHPUnit\Framework\Constraint\IsEqualIgnoringCase;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\LessThan;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\Constraint\StringEndsWith;
use PHPUnit\Framework\Constraint\StringStartsWith;
use PHPUnit\Framework\NativeType;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RestCertain\Exception\PathResolutionFailure;
use RestCertain\Internal\ResponseExpectations;
use RestCertain\Response\Response;
use RestCertain\Response\ResponseBody;
use RestCertain\Specification\RequestSpecification;
use RestCertain\Test\Str;
use Stringable;
use stdClass;

class ResponseExpectationsTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ResponseBody & StreamInterface & MockInterface $body;
    private Response & MockInterface $response;
    private ResponseExpectations $responseSpecification;

    protected function setUp(): void
    {
        /** @var ResponseBody & StreamInterface & MockInterface $body */
        $body = Mockery::mock(ResponseBody::class . ',' . StreamInterface::class);

        $this->body = $body;
        $this->response = Mockery::mock(Response::class, ['getBody' => $this->body]);
        $this->responseSpecification = new ResponseExpectations($this->response);
    }

    public function testAnd(): void
    {
        $this->assertSame($this->responseSpecification, $this->responseSpecification->and());
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueSuccessProvider')]
    public function testBodyWithSuccess(string $actualValue, array $testValue): void
    {
        $this->body->shouldReceive('asString')->andReturn($actualValue);

        $this->assertSame($this->responseSpecification, $this->responseSpecification->body(...$testValue));
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueFailureProvider')]
    public function testBodyWithFailure(string $actualValue, array $testValue): void
    {
        $this->body->shouldReceive('asString')->andReturn($actualValue);

        $this->expectException(AssertionFailedError::class);

        $this->responseSpecification->body(...$testValue);
    }

    /**
     * @param Constraint | Stringable | stdClass | array<mixed> | bool | float | int | string | null $actualValue
     * @param array<Constraint | Stringable | stdClass | array<mixed> | bool | float | int | string | null> $testValue
     */
    #[DataProvider('pathSuccessProvider')]
    public function testPathWithSuccess(
        Constraint | Stringable | stdClass | array | bool | float | int | string | null $actualValue,
        array $testValue,
    ): void {
        $this->response->shouldReceive('path')->with('foo.bar')->andReturn($actualValue);

        $this->assertSame(
            $this->responseSpecification,
            $this->responseSpecification->path('foo.bar', ...$testValue),
        );
    }

    /**
     * @param Constraint | Stringable | stdClass | array<mixed> | bool | float | int | string | null $actualValue
     * @param array<Constraint | Stringable | stdClass | array<mixed> | bool | float | int | string | null> $testValue
     */
    #[DataProvider('pathFailureProvider')]
    public function testPathWithFailure(
        Constraint | Stringable | stdClass | array | bool | float | int | string | null $actualValue,
        array $testValue,
    ): void {
        $this->response->shouldReceive('path')->with('foo.bar')->andReturn($actualValue);

        $this->expectException(AssertionFailedError::class);

        $this->responseSpecification->path('foo.bar', ...$testValue);
    }

    public function testPathWithPathResolutionFailure(): void
    {
        $this->response->shouldReceive('path')->with('foo.bar.baz')->andThrow(new PathResolutionFailure());

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that response body path "foo.bar.baz" exists:');

        $this->responseSpecification->path('foo.bar.baz', 'foo', 'bar', 'baz');
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueSuccessProvider')]
    public function testContentTypeWithSuccess(string $actualValue, array $testValue): void
    {
        $this->response->shouldReceive('getHeaderLine')->with('content-type')->andReturn($actualValue);

        $this->assertSame($this->responseSpecification, $this->responseSpecification->contentType(...$testValue));
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueFailureProvider')]
    public function testContentTypeWithFailure(string $actualValue, array $testValue): void
    {
        $this->response->shouldReceive('getHeaderLine')->with('content-type')->andReturn($actualValue);

        $this->expectException(AssertionFailedError::class);

        $this->responseSpecification->contentType(...$testValue);
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueSuccessProvider')]
    public function testCookieWithSuccess(string $actualValue, array $testValue): void
    {
        $this->response->shouldReceive('getCookie')->with('my-cookie')->andReturn($actualValue);

        $this->assertSame(
            $this->responseSpecification,
            $this->responseSpecification->cookie('my-cookie', ...$testValue),
        );
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueFailureProvider')]
    public function testCookieWithFailure(string $actualValue, array $testValue): void
    {
        $this->response->shouldReceive('getCookie')->with('my-cookie')->andReturn($actualValue);

        $this->expectException(AssertionFailedError::class);

        $this->responseSpecification->cookie('my-cookie', ...$testValue);
    }

    public function testCookieIsSet(): void
    {
        $this->response->shouldReceive('getCookie')->with('my-cookie')->andReturn('');

        $this->assertSame($this->responseSpecification, $this->responseSpecification->cookie('my-cookie'));
    }

    public function testCookieIsNotSet(): void
    {
        $this->response->shouldReceive('getCookie')->with('my-cookie')->andReturn(null);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that cookie "my-cookie" is set.');

        $this->responseSpecification->cookie('my-cookie');
    }

    public function testCookiesWithSuccess(): void
    {
        $this->response->shouldReceive('getCookie')->with('aCookie1')->andReturn('foo');
        $this->response->shouldReceive('getCookie')->with('aCookie2')->andReturn('foo bar');
        $this->response->shouldReceive('getCookie')->with('aCookie3')->andReturn('foo bar baz');
        $this->response->shouldReceive('getCookie')->with('aCookie4')->andReturn('foo bar baz qux quux corge grault');

        $this->assertSame($this->responseSpecification, $this->responseSpecification->cookies([
            'aCookie1' => 'foo',
            'aCookie2' => new Str('foo bar'),
            'aCookie3' => new StringContains('bar'),
            'aCookie4' => [
                new StringContains('baz'),
                new StringContains('qux'),
                new StringStartsWith('foo bar'),
                'foo bar baz qux quux corge grault',
            ],
        ]));
    }

    public function testCookiesWithFailure(): void
    {
        $this->response->shouldReceive('getCookie')->with('aCookie1')->andReturn('foo');
        $this->response->shouldReceive('getCookie')->with('aCookie2')->andReturn('foo bar');
        $this->response->shouldReceive('getCookie')->with('aCookie3')->andReturn('foo bar baz');
        $this->response->shouldReceive('getCookie')->with('aCookie4')->andReturn('foo bar baz qux quux corge grault');

        $this->expectException(AssertionFailedError::class);

        $this->responseSpecification->cookies([
            'aCookie1' => 'foo',
            'aCookie2' => new Str('foo bar'),
            'aCookie3' => new StringContains('bar'),
            'aCookie4' => [
                new StringContains('baz'),
                new StringContains('qux'),
                new StringStartsWith('foo bar'),
                'foo bar baz qux quux corge grault',
                new StringEndsWith('corge grault garply'), // This is where it should fail.
            ],
        ]);
    }

    public function testExpect(): void
    {
        $this->assertSame($this->responseSpecification, $this->responseSpecification->expect());
    }

    public function testGiven(): void
    {
        $requestSpecification = Mockery::mock(RequestSpecification::class);

        $this->responseSpecification->setRequestSpecification($requestSpecification);

        $this->assertSame($requestSpecification, $this->responseSpecification->given());
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueSuccessProvider')]
    public function testHeaderWithSuccess(string $actualValue, array $testValue): void
    {
        $this->response->shouldReceive('getHeaderLine')->with('my-header')->andReturn($actualValue);

        $this->assertSame(
            $this->responseSpecification,
            $this->responseSpecification->header('my-header', ...$testValue),
        );
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueFailureProvider')]
    public function testHeaderWithFailure(string $actualValue, array $testValue): void
    {
        $this->response->shouldReceive('getHeaderLine')->with('my-header')->andReturn($actualValue);

        $this->expectException(AssertionFailedError::class);

        $this->responseSpecification->header('my-header', ...$testValue);
    }

    public function testHeadersWithSuccess(): void
    {
        $this->response->shouldReceive('getHeaderLine')->with('aHeader1')->andReturn('foo');
        $this->response->shouldReceive('getHeaderLine')->with('aHeader2')->andReturn('foo bar');
        $this->response->shouldReceive('getHeaderLine')->with('aHeader3')->andReturn('foo bar baz');
        $this->response->shouldReceive('getHeaderLine')->with('aHeader4')->andReturn('foo bar baz qux quux corge');

        $this->assertSame($this->responseSpecification, $this->responseSpecification->headers([
            'aHeader1' => 'foo',
            'aHeader2' => new Str('foo bar'),
            'aHeader3' => new StringContains('bar'),
            'aHeader4' => [
                new StringContains('baz'),
                new StringContains('qux'),
                new StringStartsWith('foo bar'),
                'foo bar baz qux quux corge',
            ],
        ]));
    }

    public function testHeadersWithFailure(): void
    {
        $this->response->shouldReceive('getHeaderLine')->with('aHeader1')->andReturn('foo');
        $this->response->shouldReceive('getHeaderLine')->with('aHeader2')->andReturn('foo bar');
        $this->response->shouldReceive('getHeaderLine')->with('aHeader3')->andReturn('foo bar baz');
        $this->response->shouldReceive('getHeaderLine')->with('aHeader4')->andReturn('foo bar baz qux quux corge');

        $this->expectException(AssertionFailedError::class);

        $this->responseSpecification->headers([
            'aHeader1' => 'foo',
            'aHeader2' => new Str('foo bar'),
            'aHeader3' => new StringContains('bar'),
            'aHeader4' => [
                new StringContains('baz'),
                new StringContains('qux'),
                new StringStartsWith('foo bar'),
                'foo bar baz qux quux corge',
                new StringEndsWith('corge grault'), // This is where it should fail.
            ],
        ]);
    }

    public function testRequest(): void
    {
        $requestSpecification = Mockery::mock(RequestSpecification::class);

        $this->responseSpecification->setRequestSpecification($requestSpecification);

        $this->assertSame($requestSpecification, $this->responseSpecification->request());
    }

    public function testResponse(): void
    {
        $this->assertSame($this->responseSpecification, $this->responseSpecification->response());
    }

    public function testSetRequestSpecification(): void
    {
        $requestSpecification = Mockery::mock(RequestSpecification::class);

        $this->assertSame(
            $this->responseSpecification,
            $this->responseSpecification->setRequestSpecification($requestSpecification),
        );
    }

    public function testSetRequestSpecificationOnConstructor(): void
    {
        $requestSpecification = Mockery::mock(RequestSpecification::class);
        $responseSpecification = new ResponseExpectations($this->response, $requestSpecification);

        $this->assertSame($requestSpecification, $responseSpecification->request());
    }

    public function testStatusCodeWithSuccess(): void
    {
        $this->response->shouldReceive('getStatusCode')->andReturn(202);

        $this->assertSame(
            $this->responseSpecification,
            $this->responseSpecification->statusCode(
                new IsType(NativeType::Int),
                new GreaterThan(200),
                new LessThan(300),
                202,
            ),
        );
    }

    public function testStatusCodeWithFailure(): void
    {
        $this->response->shouldReceive('getStatusCode')->andReturn(204);

        $this->expectException(AssertionFailedError::class);

        $this->responseSpecification->statusCode(
            new IsType(NativeType::Int),
            new GreaterThan(200),
            new LessThan(300),
            202, // This is where it should fail.
        );
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueSuccessProvider')]
    public function testStatusLineWithSuccess(string $actualValue, array $testValue): void
    {
        $this->response->shouldReceive('getStatusLine')->andReturn($actualValue);

        $this->assertSame(
            $this->responseSpecification,
            $this->responseSpecification->statusLine(...$testValue),
        );
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueFailureProvider')]
    public function testStatusLineWithFailure(string $actualValue, array $testValue): void
    {
        $this->response->shouldReceive('getStatusLine')->andReturn($actualValue);

        $this->expectException(AssertionFailedError::class);

        $this->responseSpecification->statusLine(...$testValue);
    }

    public function testThat(): void
    {
        $this->assertSame($this->responseSpecification, $this->responseSpecification->that());
    }

    public function testThen(): void
    {
        $this->assertSame($this->responseSpecification, $this->responseSpecification->then());
    }

    public function testTimeWithSuccess(): void
    {
        $this->response->shouldReceive('getTime')->andReturn(582);

        $this->assertSame(
            $this->responseSpecification,
            $this->responseSpecification->time(
                new IsType(NativeType::Int),
                new LessThan(1000),
            ),
        );
    }

    public function testTimeWithFailure(): void
    {
        $this->response->shouldReceive('getTime')->andReturn(1042);

        $this->expectException(AssertionFailedError::class);

        $this->responseSpecification->time(
            new IsType(NativeType::Int),
            new LessThan(1000), // This is where it should fail.
        );
    }

    public function testWhen(): void
    {
        $requestSpecification = Mockery::mock(RequestSpecification::class);

        $this->responseSpecification->setRequestSpecification($requestSpecification);

        $this->assertSame($requestSpecification, $this->responseSpecification->when());
    }

    public function testWith(): void
    {
        $requestSpecification = Mockery::mock(RequestSpecification::class);

        $this->responseSpecification->setRequestSpecification($requestSpecification);

        $this->assertSame($requestSpecification, $this->responseSpecification->with());
    }

    /**
     * @return array<array{actualValue: string, testValue: array<Constraint | Stringable | string>}>
     */
    public static function generalValueSuccessProvider(): array
    {
        return [
            [
                'actualValue' => 'foo',
                'testValue' => ['foo'],
            ],
            [
                'actualValue' => 'foo',
                'testValue' => [new Str('foo')],
            ],
            [
                'actualValue' => 'foo',
                'testValue' => [new IsEqualIgnoringCase('FOO')],
            ],
            [
                'actualValue' => 'foo bar',
                'testValue' => [
                    new StringContains('foo'),
                    new StringContains('bar'),
                    new Str('foo bar'),
                    'foo bar',
                ],
            ],
        ];
    }

    /**
     * @return array<array{actualValue: string, testValue: array<Constraint | Stringable | string>}>
     */
    public static function generalValueFailureProvider(): array
    {
        return [
            [
                'actualValue' => 'foo',
                'testValue' => ['bar'],
            ],
            [
                'actualValue' => 'foo',
                'testValue' => [new Str('bar')],
            ],
            [
                'actualValue' => 'foo',
                'testValue' => [new IsEqualIgnoringCase('BAR')],
            ],
            [
                'actualValue' => 'foo bar',
                'testValue' => [
                    new StringContains('foo'),
                    new StringContains('bar'),
                    new Str('foo bar'),
                    'foo bar',
                    new StringContains('baz'), // This is where it should fail.
                ],
            ],
        ];
    }

    /**
     * @return array<array{
     *     actualValue: stdClass | array<mixed> | bool | float | int | string | null,
     *     testValue: array<Constraint | Stringable | stdClass | array<mixed> | bool | float | int | string | null>,
     * }>
     */
    public static function pathSuccessProvider(): array
    {
        return [
            ['actualValue' => 42.0, 'testValue' => [new IsIdentical(42.0)]],
            ['actualValue' => 'foo', 'testValue' => [new Str('foo')]],
            ['actualValue' => true, 'testValue' => [true]],
            ['actualValue' => false, 'testValue' => [false]],
            ['actualValue' => 42, 'testValue' => [42]],
            ['actualValue' => 42, 'testValue' => [42.0]],
            ['actualValue' => 42.0, 'testValue' => [42]],
            ['actualValue' => 42.0, 'testValue' => [42.0]],
            ['actualValue' => '42', 'testValue' => [42]],
            ['actualValue' => '42', 'testValue' => [42.0]],
            ['actualValue' => '42.0', 'testValue' => [42]],
            ['actualValue' => '42.0', 'testValue' => [42.0]],
            ['actualValue' => 42, 'testValue' => ['42']],
            ['actualValue' => 42, 'testValue' => ['42.0']],
            ['actualValue' => 42.0, 'testValue' => ['42']],
            ['actualValue' => 42.0, 'testValue' => ['42.0']],
            ['actualValue' => [1, 2, 3, 4, 5], 'testValue' => [[5, 4, 3, 2, 1]]],
            ['actualValue' => ['a' => 1, 'b' => 3, 'c' => 2], 'testValue' => [['b' => 3, 'a' => 1, 'c' => 2]]],
            ['actualValue' => 'foo', 'testValue' => ['foo']],
            ['actualValue' => null, 'testValue' => [null]],
            [
                'actualValue' => (object) ['foo' => 'bar', 'baz' => 'quux'],
                'testValue' => [(object) ['baz' => 'quux', 'foo' => 'bar']],
            ],
        ];
    }

    /**
     * @return array<array{
     *     actualValue: stdClass | array<mixed> | bool | float | int | string | null,
     *     testValue: array<Constraint | Stringable | stdClass | array<mixed> | bool | float | int | string | null>,
     * }>
     */
    public static function pathFailureProvider(): array
    {
        return [
            ['actualValue' => 42.00000000000001, 'testValue' => [new IsIdentical(42.0)]],
            ['actualValue' => 'bar', 'testValue' => [new Str('foo')]],
            ['actualValue' => false, 'testValue' => [true]],
            ['actualValue' => true, 'testValue' => [false]],
            ['actualValue' => 43, 'testValue' => [42]],
            ['actualValue' => 42.00000000000001, 'testValue' => [42.0]],
            ['actualValue' => '43', 'testValue' => [42]],
            ['actualValue' => '42.00000000000001', 'testValue' => [42.0]],
            ['actualValue' => 43, 'testValue' => ['42']],
            ['actualValue' => 42.00000000000001, 'testValue' => ['42.0']],
            ['actualValue' => [1, 2, 3], 'testValue' => [[5, 4, 3, 2, 1]]],
            ['actualValue' => ['a' => 1, 'c' => 2], 'testValue' => [['b' => 3, 'a' => 1, 'c' => 2]]],
            ['actualValue' => 'bar', 'testValue' => ['foo']],
            ['actualValue' => 123, 'testValue' => [null]],
            [
                'actualValue' => (object) ['foo' => 'bar', 'baz' => 'qux'],
                'testValue' => [(object) ['baz' => 'quux', 'foo' => 'bar']],
            ],
        ];
    }
}
