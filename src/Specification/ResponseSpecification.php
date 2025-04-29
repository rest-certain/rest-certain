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

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use RestCertain\Request\Sender;
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
     * @param Constraint | Stringable | string $expectation A matcher to validate the value of the entire body.
     * @param Constraint | Stringable | string ...$additionalExpectations Additional matchers to validate the value
     *     of the entire body.
     *
     * @return $this
     *
     * @throws AssertionFailedError
     */
    public function body(
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static;

    /**
     * An expectation to validate the given response path in the response body against the given matchers.
     *
     * @link https://www.rfc-editor.org/rfc/rfc9535.html JSONPath
     * @link https://jmespath.org JMESPath
     *
     * @param string $path A body path in JSONPath or JMESPath syntax.
     * @param Constraint | Stringable | bool | float | int | mixed[] | string | null $expectation A matcher to validate
     *     the value at the given path.
     * @param Constraint | Stringable | bool | float | int | mixed[] | string | null ...$additionalExpectations
     *     Additional matchers to validate the value at the given path.
     *
     * @return $this
     *
     * @throws AssertionFailedError
     */
    public function bodyPath(
        string $path,
        Constraint | Stringable | array | bool | float | int | string | null $expectation,
        Constraint | Stringable | array | bool | float | int | string | null ...$additionalExpectations,
    ): static;

    /**
     * An expectation to validate the given response Content-Type against the given value or matcher.
     *
     * @param Constraint | Stringable | string $expectation A matcher to validate the value of the content type header.
     * @param Constraint | Stringable | string ...$additionalExpectations Additional matchers to validate the value
     *     of the content type header.
     *
     * @return $this
     *
     * @throws AssertionFailedError
     */
    public function contentType(
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static;

    /**
     * An expectation to validate the given response cookie against the given value or matcher.
     *
     * If the $expectation is null, this validates whether the cookie exists, instead, and any additional expectations
     * are ignored.
     *
     * @param Constraint | Stringable | string | null $expectation A matcher to validate the value of the named cookie.
     * @param Constraint | Stringable | string ...$additionalExpectations Additional matchers to validate the value
     *     of the named cookie.
     *
     * @return $this
     *
     * @throws AssertionFailedError
     */
    public function cookie(
        string $name,
        Constraint | Stringable | string | null $expectation = null,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static;

    /**
     * An expectation to validate the given response cookies against the given values or matchers.
     *
     * @param array<string, Constraint | Stringable | string | array<Constraint | Stringable | string>> $expectations
     *     Matchers to validate multiple named cookies.
     *
     * @return $this
     *
     * @throws AssertionFailedError
     */
    public function cookies(array $expectations): static;

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
     * @param Constraint | Stringable | string $expectation A matcher to validate the value of the named header.
     * @param Constraint | Stringable | string ...$additionalExpectations Additional matchers to validate the value
     *     of the named header.
     *
     * @return $this
     *
     * @throws AssertionFailedError
     */
    public function header(
        string $name,
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static;

    /**
     * An expectation to validate the given response headers against the given values or matchers.
     *
     * @param array<string, Constraint | Stringable | string | array<Constraint | Stringable | string>> $expectations
     *     Matchers to validate multiple named headers.
     *
     * @return $this
     *
     * @throws AssertionFailedError
     */
    public function headers(array $expectations): static;

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
     * Sets the request specification to use with this response specification.
     *
     * @return $this
     */
    public function setRequestSpecification(RequestSpecification $requestSpecification): static;

    /**
     * An expectation to validate the given response status code against the given value or matcher.
     *
     * @param Constraint | int $expectation A matcher to validate the value of the status code.
     * @param Constraint | int ...$additionalExpectations Additional matchers to validate the value of the status code.
     *
     * @return $this
     *
     * @throws AssertionFailedError
     */
    public function statusCode(Constraint | int $expectation, Constraint | int ...$additionalExpectations): static;

    /**
     * An expectation to validate the given response status line against the given value or matcher.
     *
     * @param Constraint | Stringable | string $expectation A matcher to validate the value of the status line.
     * @param Constraint | Stringable | string ...$additionalExpectations Additional matchers to validate the value of
     *     the status line.
     *
     * @return $this
     *
     * @throws AssertionFailedError
     */
    public function statusLine(
        Constraint | Stringable | string $expectation,
        Constraint | Stringable | string ...$additionalExpectations,
    ): static;

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
     * @param Constraint $expectation A matcher to validate the value of the response time.
     * @param Constraint ...$additionalExpectations Additional matchers to validate the value of the response time.
     *
     * @return $this
     *
     * @throws AssertionFailedError
     */
    public function time(Constraint $expectation, Constraint ...$additionalExpectations): static;

    /**
     * Returns the request sender to use in sending the request.
     */
    public function when(): Sender;

    /**
     * Returns the request specification to use in defining the properties of the request.
     */
    public function with(): RequestSpecification;
}
