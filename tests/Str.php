<?php

declare(strict_types=1);

namespace RestCertain\Test;

use Stringable;

final readonly class Str implements Stringable
{
    public function __construct(private string $value)
    {
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->value;
    }
}
