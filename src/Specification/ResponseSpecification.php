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

namespace RestCertain\Specification;

use Hamcrest\Matcher;
use PHPUnit\Framework\Constraint\Constraint;
use RestCertain\Response\Response;
use Stringable;

interface ResponseSpecification
{
    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function and(): static;

    /**
     * An expectation to validate the given response body against the given matchers.
     *
     * @param string | null $path An optional body path in JSONPath syntax; if provided, only this path will be
     *     validated for these constraints or matchers. If not provided, the entire body will be validated.
     *
     * @return $this
     */
    public function body(?string $path = null, Constraint | Matcher ...$expectedValue): static;

    /**
     * An expectation to validate the given response Content-Type against the given value or matcher.
     *
     * @return $this
     */
    public function contentType(Constraint | Matcher | Stringable | string $expectedValue): static;

    /**
     * An expectation to validate the given response cookie against the given value or matcher.
     *
     * If the $expectedValue is null, this validates whether the cookie exists, instead.
     *
     * @return $this
     */
    public function cookie(
        string $name,
        Constraint | Matcher | Stringable | string | null $expectedValue = null,
    ): static;

    /**
     * An expectation to validate the given response cookies against the given values or matchers.
     *
     * @param array<string, Constraint | Matcher | Stringable | string> $expectedCookies
     *
     * @return $this
     */
    public function cookies(array $expectedCookies): static;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function expect(): static;

    /**
     * Returns the request specification to use in defining the properties of the request.
     */
    public function given(): RequestSpecification;

    /**
     * An expectation to validate the given response header against the given value or matcher.
     *
     * @return $this
     */
    public function header(string $name, Constraint | Matcher | Stringable | string $expectedValue): static;

    /**
     * An expectation to validate the given response headers against the given values or matchers.
     *
     * @param array<string, Constraint | Matcher | Stringable | string> $expectedHeaders
     *
     * @return $this
     */
    public function headers(array $expectedHeaders): static;

    /**
     * Returns the request specification to use in defining the properties of the request.
     */
    public function request(): RequestSpecification;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function response(): static;

    /**
     * An expectation to validate the given response status code against the given value or matcher.
     *
     * @return $this
     */
    public function statusCode(Constraint | Matcher | int $expectedValue): static;

    /**
     * An expectation to validate the given response status line against the given value or matcher.
     *
     * @return $this
     */
    public function statusLine(Constraint | Matcher | Stringable | string $expectedValue): static;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function that(): static;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function then(): static;

    /**
     * An expectation to validate the given response time against the given matcher.
     *
     * @return $this
     */
    public function time(Constraint | Matcher $matcher): static;

    /**
     * Validates the given response against this specification.
     */
    public function validate(Response $response): Response;

    /**
     * Returns the request sender to use in sending the request.
     */
    public function when(): RequestSender;

    /**
     * Returns the request specification to use in defining the properties of the request.
     */
    public function with(): RequestSpecification;
}
