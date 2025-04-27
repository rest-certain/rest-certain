<?php

declare(strict_types=1);

namespace RestCertain\Test\Behavior;

use Ciareis\Bypass\Bypass;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;
use RestCertain\Config;
use RestCertain\RestCertain;

use const PHP_INT_MAX;

abstract class BehaviorTestCase extends TestCase
{
    protected Bypass $bypass;

    #[Before(PHP_INT_MAX)]
    protected function startBypass(): void
    {
        $this->bypass = Bypass::open();
    }

    #[Before(PHP_INT_MAX - 10)]
    protected function configureRestCertain(): void
    {
        $config = new Config(port: $this->bypass->getPort());
        RestCertain::$config = $config;
    }

    #[After]
    protected function stopBypass(): void
    {
        $this->bypass->stop();
    }

    #[After]
    protected function unconfigureRestCertain(): void
    {
        RestCertain::$config = null;
    }
}
