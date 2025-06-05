<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\GreaterThan;
use PHPUnit\Framework\Constraint\IsEqualIgnoringCase;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\LessThan;
use PHPUnit\Framework\NativeType;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RestCertain\Internal\ResponseValidator;
use RestCertain\Response\Response;
use RestCertain\Response\ResponseBody;
use RestCertain\Specification\ResponseSpecification;
use RestCertain\Test\Str;

class ResponseValidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ResponseSpecification & MockInterface $responseSpecification;
    private ResponseValidator $validatableResponse;

    protected function setUp(): void
    {
        $response = Mockery::mock(Response::class, [
            'body' => Mockery::mock(ResponseBody::class . ',' . StreamInterface::class),
        ]);
        $this->responseSpecification = Mockery::spy(ResponseSpecification::class);
        $this->validatableResponse = new ResponseValidator($response, $this->responseSpecification);
    }

    public function testAnd(): void
    {
        $this->assertSame($this->validatableResponse, $this->validatableResponse->and());
    }

    public function testAssertThat(): void
    {
        $this->assertSame($this->validatableResponse, $this->validatableResponse->assertThat());
    }

    #[DataProvider('singularCaseMethodsProvider')]
    public function testSingularCaseMethods(string $method): void
    {
        $value1 = 'foo';
        $value2 = new Str('foo');
        $value3 = new IsEqualIgnoringCase('FOO');

        $this->assertSame(
            $this->validatableResponse,
            $this->validatableResponse->{$method}($value1, $value2, $value3),
        );

        $this->responseSpecification->shouldHaveReceived($method, [$value1, $value2, $value3]);
    }

    /**
     * @return array<array{method: string}>
     */
    public static function singularCaseMethodsProvider(): array
    {
        return [
            ['method' => 'body'],
            ['method' => 'contentType'],
            ['method' => 'cookie'],
            ['method' => 'header'],
            ['method' => 'path'],
            ['method' => 'statusLine'],
        ];
    }

    #[DataProvider('multipleCaseMethodsProvider')]
    public function testMultipleCaseMethods(string $method): void
    {
        $value1 = 'foo';
        $value2 = new Str('foo');
        $value3 = new IsEqualIgnoringCase('FOO');

        $valuesToCheck = [
            'value1' => [$value1, $value2, $value3],
            'value2' => [$value1, $value2, $value3],
            'value3' => [$value1, $value2, $value3],
        ];

        $this->assertSame(
            $this->validatableResponse,
            $this->validatableResponse->{$method}($valuesToCheck),
        );

        $this->responseSpecification->shouldHaveReceived($method, [$valuesToCheck]);
    }

    /**
     * @return array<array{method: string}>
     */
    public static function multipleCaseMethodsProvider(): array
    {
        return [
            ['method' => 'cookies'],
            ['method' => 'headers'],
        ];
    }

    public function testStatusCode(): void
    {
        $value1 = 123;
        $value2 = new GreaterThan(100);
        $value3 = new LessThan(200);

        $this->assertSame(
            $this->validatableResponse,
            $this->validatableResponse->statusCode($value1, $value2, $value3),
        );

        $this->responseSpecification->shouldHaveReceived('statusCode', [$value1, $value2, $value3]);
    }

    public function testTime(): void
    {
        $value1 = new IsType(NativeType::Int);
        $value2 = new GreaterThan(100);
        $value3 = new LessThan(1000);

        $this->assertSame(
            $this->validatableResponse,
            $this->validatableResponse->time($value1, $value2, $value3),
        );

        $this->responseSpecification->shouldHaveReceived('time', [$value1, $value2, $value3]);
    }

    public function testUsing(): void
    {
        $this->assertSame($this->validatableResponse, $this->validatableResponse->using());
    }
}
