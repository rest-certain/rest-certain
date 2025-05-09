<?php

declare(strict_types=1);

namespace RestCertain\Test;

use Ciareis\Bypass\Bypass;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use RestCertain\Config;
use RestCertain\RestCertain;

use const PHP_INT_MAX;

trait MockWebServer
{
    /**
     * The mock web server.
     */
    private Bypass $server;

    #[Before(PHP_INT_MAX)]
    protected function startBypass(): void
    {
        $this->server = Bypass::open();
    }

    #[Before(PHP_INT_MAX - 10)]
    protected function configureRestCertain(): void
    {
        $config = new Config(port: $this->server->getPort());
        RestCertain::$config = $config;
    }

    #[After]
    protected function stopBypass(): void
    {
        $this->server->stop();
    }

    #[After]
    protected function unconfigureRestCertain(): void
    {
        RestCertain::$config = null;
    }

    /**
     * Returns the mock web server.
     */
    private function server(): Bypass
    {
        return $this->server;
    }
}
