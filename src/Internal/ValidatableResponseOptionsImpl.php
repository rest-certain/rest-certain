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

use Override;
use PHPUnit\Framework\Constraint\Constraint;
use RestCertain\Response\ValidatableResponseOptions;
use RestCertain\Specification\ResponseSpecification;
use Stringable;

/**
 * @internal
 */
final readonly class ValidatableResponseOptionsImpl implements ValidatableResponseOptions
{
    public function __construct(private ResponseSpecification $responseSpecification)
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

    #[Override] public function body(
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->responseSpecification->body($expectation, ...$additionalExpectations);

        return $this;
    }

    #[Override] public function bodyPath(
        string $path,
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->responseSpecification->bodyPath($path, $expectation, ...$additionalExpectations);

        return $this;
    }

    #[Override] public function contentType(
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->responseSpecification->contentType($expectation, ...$additionalExpectations);

        return $this;
    }

    #[Override] public function cookie(
        string $name,
        Constraint | Stringable | string | null $expectation = null,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->responseSpecification->cookie($name, $expectation, ...$additionalExpectations);

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function cookies(array $expectations): static
    {
        $this->responseSpecification->cookies($expectations);

        return $this;
    }

    #[Override] public function header(
        string $name,
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->responseSpecification->header($name, $expectation, ...$additionalExpectations);

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function headers(array $expectations): static
    {
        $this->responseSpecification->headers($expectations);

        return $this;
    }

    #[Override] public function statusCode(
        Constraint | int $expectation,
        Constraint | int ...$additionalExpectations,
    ): static {
        $this->responseSpecification->statusCode($expectation, ...$additionalExpectations);

        return $this;
    }

    #[Override] public function statusLine(
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static {
        $this->responseSpecification->statusLine($expectation, ...$additionalExpectations);

        return $this;
    }

    #[Override] public function time(Constraint $expectation, Constraint ...$additionalExpectations): static
    {
        $this->responseSpecification->time($expectation, ...$additionalExpectations);

        return $this;
    }

    #[Override] public function using(): static
    {
        return $this;
    }
}
