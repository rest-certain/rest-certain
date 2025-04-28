<?php

/**
 * This file is part of REST Certain
 *
 * REST Certain is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * REST Certain is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with REST Certain. If not, see <https://www.gnu.org/licenses/>.
 *
 * @copyright Copyright (c) REST Certain Contributors <https://rest-certain.dev>
 * @license https://opensource.org/license/lgpl-3-0/ GNU Lesser General Public License version 3 or later
 */

declare(strict_types=1);

namespace RestCertain\Internal\Type;

use Iterator;
use Override;

use function strlen;

/**
 * A value parsed from a JSON body.
 *
 * @implements Iterator<int, string>
 */
final class ByteArray implements Iterator, ParsedType
{
    private int $position = 0;
    private readonly int $length;

    public function __construct(private readonly string $value)
    {
        $this->length = strlen($this->value);
    }

    #[Override] public function __toString(): string
    {
        return $this->value;
    }

    #[Override] public function current(): string
    {
        return $this->value[$this->position];
    }

    #[Override] public function getValue(): string
    {
        return $this->value;
    }

    #[Override] public function key(): int
    {
        return $this->position;
    }

    #[Override] public function next(): void
    {
        $this->position++;
    }

    #[Override] public function rewind(): void
    {
        $this->position = 0;
    }

    #[Override] public function valid(): bool
    {
        return $this->position < $this->length;
    }
}
