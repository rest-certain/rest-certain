<?php

declare(strict_types=1);

namespace RestCertain\Test;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Configures and returns a mock object with proper type-hinting
     * for static analysis tools
     *
     * @param class-string<T> $class
     * @param mixed ...$arguments The arguments to pass along to the mock factory.
     *
     * @return T & MockInterface
     *
     * @template T
     */
    public function mockery(string $class, mixed ...$arguments): MockInterface
    {
        /** @var T & MockInterface */
        return Mockery::mock($class, ...$arguments);
    }

    /**
     * Configures and returns a mock object for spying with proper type-hinting
     * for static analysis tools
     *
     * @param class-string<T> $class
     * @param mixed ...$arguments The arguments to pass along to the mock factory.
     *
     * @return T & MockInterface
     *
     * @template T
     */
    public function mockerySpy(string $class, mixed ...$arguments): MockInterface
    {
        /** @var T & MockInterface */
        return Mockery::spy($class, ...$arguments);
    }
}
