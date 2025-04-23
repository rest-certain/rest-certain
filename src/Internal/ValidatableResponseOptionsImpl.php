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

namespace RestCertain\Internal;

use LogicException;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use RestCertain\Response\ValidatableResponseOptions;
use Stringable;

/**
 * @internal
 */
final readonly class ValidatableResponseOptionsImpl implements ValidatableResponseOptions
{
    public function __construct()
    {
    }

    #[Override] public function and(): static
    {
        return $this;
    }

    #[Override] public function assertThat(): static
    {
        return $this;
    }

    #[Override] public function body(Constraint | Stringable | string ...$expectedValue): static
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function bodyPath(string $path, Constraint | Stringable | string ...$expectedValue): static
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function contentType(Constraint | Stringable | string $expectedValue): static
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function cookie(
        string $name,
        Constraint | Stringable | string | null $expectedValue = null,
    ): static {
        throw new LogicException('Not implemented yet');
    }

    /**
     * @inheritDoc
     */
    #[Override] public function cookies(array $expectedCookies): static
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function header(string $name, Constraint | Stringable | string $expectedValue): static
    {
        throw new LogicException('Not implemented yet');
    }

    /**
     * @inheritDoc
     */
    #[Override] public function headers(array $expectedHeaders): static
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function statusCode(Constraint | int $expectedValue): static
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function statusLine(Constraint | Stringable | string $expectedValue): static
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function time(Constraint $matcher): static
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function using(): static
    {
        return $this;
    }
}
