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

namespace RestCertain\Response;

use PHPUnit\Framework\Constraint\Constraint;
use Stringable;

interface ValidatableResponseOptions
{
    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function and(): static;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function assertThat(): static;

    /**
     * An expectation to validate the given response body against the given matchers.
     *
     * @return $this
     */
    public function body(Constraint | Stringable | string ...$expectedValue): static;

    /**
     * An expectation to validate the given response path in the response body against the given matchers.
     *
     * @param string $path A body path in JSONPath syntax.
     *
     * @return $this
     */
    public function bodyPath(string $path, Constraint | Stringable | string ...$expectedValue): static;

    /**
     * An expectation to validate the given response Content-Type against the given value or matcher.
     *
     * @return $this
     */
    public function contentType(Constraint | Stringable | string $expectedValue): static;

    /**
     * An expectation to validate the given response cookie against the given value or matcher.
     *
     * If the $expectedValue is null, this validates whether the cookie exists, instead.
     *
     * @return $this
     */
    public function cookie(
        string $name,
        Constraint | Stringable | string | null $expectedValue = null,
    ): static;

    /**
     * An expectation to validate the given response cookies against the given values or matchers.
     *
     * @param array<string, Constraint | Stringable | string> $expectedCookies
     *
     * @return $this
     */
    public function cookies(array $expectedCookies): static;

    /**
     * An expectation to validate the given response header against the given value or matcher.
     *
     * @return $this
     */
    public function header(string $name, Constraint | Stringable | string $expectedValue): static;

    /**
     * An expectation to validate the given response headers against the given values or matchers.
     *
     * @param array<string, Constraint | Stringable | string> $expectedHeaders
     *
     * @return $this
     */
    public function headers(array $expectedHeaders): static;

    /**
     * An expectation to validate the given response status code against the given value or matcher.
     *
     * @return $this
     */
    public function statusCode(Constraint | int $expectedValue): static;

    /**
     * An expectation to validate the given response status line against the given value or matcher.
     *
     * @return $this
     */
    public function statusLine(Constraint | Stringable | string $expectedValue): static;

    /**
     * An expectation to validate the given response time against the given matcher.
     *
     * @return $this
     */
    public function time(Constraint $matcher): static;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function using(): static;
}
