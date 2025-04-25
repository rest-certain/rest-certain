<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqualIgnoringCase;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\Constraint\StringEndsWith;
use PHPUnit\Framework\Constraint\StringStartsWith;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RestCertain\Internal\ResponseSpecificationImpl;
use RestCertain\Response\Response;
use RestCertain\Response\ResponseBody;
use RestCertain\Test\Str;
use Stringable;

class ResponseSpecificationImplTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ResponseBody & StreamInterface & MockInterface $body;
    private Response & MockInterface $response;
    private ResponseSpecificationImpl $responseSpecification;

    protected function setUp(): void
    {
        /** @var ResponseBody & StreamInterface & MockInterface $body */
        $body = Mockery::mock(ResponseBody::class . ',' . StreamInterface::class);

        $this->body = $body;
        $this->response = Mockery::mock(Response::class, ['getBody' => $this->body]);
        $this->responseSpecification = new ResponseSpecificationImpl($this->response);
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

        $this->expectException(ExpectationFailedException::class);

        $this->responseSpecification->body(...$testValue);
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

        $this->expectException(ExpectationFailedException::class);

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

        $this->expectException(ExpectationFailedException::class);

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

        $this->expectException(ExpectationFailedException::class);
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

        $this->expectException(ExpectationFailedException::class);

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

        $this->expectException(ExpectationFailedException::class);

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

        $this->expectException(ExpectationFailedException::class);

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
}
