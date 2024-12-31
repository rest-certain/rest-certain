<?php

declare(strict_types=1);

namespace RestCertain\Test;

use JsonSerializable;
use Override;

final readonly class Json implements JsonSerializable
{
    /**
     * @param array<scalar | array<scalar | null> | null> | bool | int | float | string | null $value
     */
    public function __construct(private array | bool | int | float | string | null $value)
    {
    }

    /**
     * @return array<scalar | array<scalar | null> | null> | bool | int | float | string | null
     *
     * @inheritDoc
     */
    #[Override] public function jsonSerialize(): array | bool | int | float | string | null
    {
        return $this->value;
    }
}
