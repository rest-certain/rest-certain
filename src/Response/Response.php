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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

interface Response extends ResponseBody, ResponseInterface, Validatable
{
    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function andReturn(): static;

    /**
     * Returns the response body.
     *
     * @see self::getBody()
     */
    public function body(): ResponseBody & StreamInterface;

    /**
     * Returns the value of the Content-Type header or null if the Content-Type header does not exist.
     *
     * @see self::getContentType()
     */
    public function contentType(): ?string;

    /**
     * Returns the string value of the named cookie or null if the cookie does not exist.
     */
    public function cookie(string $name): ?string;

    /**
     * Returns an associative array of the cookies available on the response.
     *
     * @return array<string, string>
     */
    public function cookies(): array;

    /**
     * Returns the response body.
     *
     * @see self::body()
     */
    public function getBody(): ResponseBody & StreamInterface;

    /**
     * Returns the value of the Content-Type header or null if the Content-Type header does not exist.
     *
     * @see self::contentType()
     */
    public function getContentType(): ?string;

    /**
     * Returns the string value of the named cookie or null if the cookie does not exist.
     *
     * @see self::cookie()
     */
    public function getCookie(string $name): ?string;

    /**
     * Returns an associative array of the cookies available on the response.
     *
     * @see self::cookies()
     *
     * @return array<string, string>
     */
    public function getCookies(): array;

    /**
     * Returns a list of one or more values for the given header name, or an empty array if the header does not exist.
     *
     * @see self::header()
     *
     * @return list<string>
     */
    public function getHeader(string $name): array;

    /**
     * Returns an associative array of the response headers.
     *
     * Each key is a header name, and each value is a list of one or more strings for that header.
     *
     * @see self::headers()
     *
     * @return array<string, list<string>>
     */
    public function getHeaders(): array;

    /**
     * Returns the status code of the response.
     *
     * @see self::statusCode()
     */
    public function getStatusCode(): int;

    /**
     * Returns the status line of the response.
     *
     * @see self::statusLine()
     */
    public function getStatusLine(): string;

    /**
     * The response time in milliseconds, or -1 if no response time could be measured.
     *
     * @see self::time()
     */
    public function getTime(): int;

    /**
     * Returns a list of one or more values for the given header name, or an empty array if the header does not exist.
     *
     * @see self::getHeader()
     *
     * @return list<string>
     */
    public function header(string $name): array;

    /**
     * Returns an associative array of the response headers.
     *
     * Each key is a header name, and each value is a list of one or more strings for that header.
     *
     * @see self::getHeaders()
     *
     * @return array<string, list<string>>
     */
    public function headers(): array;

    /**
     * Returns the status code of the response.
     *
     * @see self::getStatusCode()
     */
    public function statusCode(): int;

    /**
     * Returns the status line of the response.
     *
     * @see self::getStatusLine()
     */
    public function statusLine(): string;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function thenReturn(): static;

    /**
     * The response time in milliseconds, or -1 if no response time could be measured.
     *
     * @see self::getTime()
     */
    public function time(): int;
}
