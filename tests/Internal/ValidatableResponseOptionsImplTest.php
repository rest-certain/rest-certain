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
use RestCertain\Internal\ValidatableResponseOptionsImpl;
use RestCertain\Specification\ResponseSpecification;
use RestCertain\Test\Str;

class ValidatableResponseOptionsImplTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ResponseSpecification & MockInterface $responseSpecification;
    private ValidatableResponseOptionsImpl $validatableResponseOptions;

    protected function setUp(): void
    {
        $this->responseSpecification = Mockery::spy(ResponseSpecification::class);
        $this->validatableResponseOptions = new ValidatableResponseOptionsImpl($this->responseSpecification);
    }

    public function testAnd(): void
    {
        $this->assertSame($this->validatableResponseOptions, $this->validatableResponseOptions->and());
    }

    public function testAssertThat(): void
    {
        $this->assertSame($this->validatableResponseOptions, $this->validatableResponseOptions->assertThat());
    }

    #[DataProvider('singularCaseMethodsProvider')]
    public function testSingularCaseMethods(string $method): void
    {
        $value1 = 'foo';
        $value2 = new Str('foo');
        $value3 = new IsEqualIgnoringCase('FOO');

        $this->assertSame(
            $this->validatableResponseOptions,
            $this->validatableResponseOptions->{$method}($value1, $value2, $value3),
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
            ['method' => 'bodyPath'],
            ['method' => 'contentType'],
            ['method' => 'cookie'],
            ['method' => 'header'],
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
            $this->validatableResponseOptions,
            $this->validatableResponseOptions->{$method}($valuesToCheck),
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
            $this->validatableResponseOptions,
            $this->validatableResponseOptions->statusCode($value1, $value2, $value3),
        );

        $this->responseSpecification->shouldHaveReceived('statusCode', [$value1, $value2, $value3]);
    }

    public function testTime(): void
    {
        $value1 = new IsType(NativeType::Int);
        $value2 = new GreaterThan(100);
        $value3 = new LessThan(1000);

        $this->assertSame(
            $this->validatableResponseOptions,
            $this->validatableResponseOptions->time($value1, $value2, $value3),
        );

        $this->responseSpecification->shouldHaveReceived('time', [$value1, $value2, $value3]);
    }

    public function testUsing(): void
    {
        $this->assertSame($this->validatableResponseOptions, $this->validatableResponseOptions->using());
    }
}
