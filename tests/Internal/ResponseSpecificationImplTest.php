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

        $this->assertSame($this->responseSpecification, $this->responseSpecification->contentType($testValue[0]));
    }

    /**
     * @param array<Constraint | Stringable | string> $testValue
     */
    #[DataProvider('generalValueFailureProvider')]
    public function testContentTypeWithFailure(string $actualValue, array $testValue): void
    {
        $this->response->shouldReceive('getHeaderLine')->with('content-type')->andReturn($actualValue);

        $this->expectException(ExpectationFailedException::class);

        $this->responseSpecification->contentType($testValue[0]);
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
            $this->responseSpecification->cookie('my-cookie', $testValue[0]),
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

        $this->responseSpecification->cookie('my-cookie', $testValue[0]);
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
                    new StringContains('baz'),
                    new StringContains('foo'),
                    new StringContains('bar'),
                    new Str('foo bar'),
                    'foo bar',
                ],
            ],
        ];
    }
}
