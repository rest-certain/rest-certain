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

interface ExtractableResponse extends ExtractableResponseBody
{
    /**
     * Returns the response body.
     */
    public function body(): ExtractableResponseBody;

    /**
     * Returns the value of the Content-Type header or null if the Content-Type header does not exist.
     */
    public function contentType(): ?string;

    /**
     * Returns a single cookie value associated with the given name.
     *
     * If the cookie does not exist, null is returned.
     */
    public function cookie(string $name): ?string;

    /**
     * The response cookies as simple name-value pairs.
     *
     * @return array<string, string>
     */
    public function cookies(): array;

    /**
     * Get a single header value associated with the given name.
     *
     * If the header does not exist, an empty array is returned.
     *
     * @return string[]
     */
    public function header(string $name): array;

    /**
     * The response headers.
     *
     * @return array<string, string[]>
     */
    public function headers(): array;

    /**
     * Returns the response as a PSR-7 response object.
     */
    public function response(): ResponseInterface;

    /**
     * Returns the status code of the response.
     */
    public function statusCode(): int;

    /**
     * Returns the status line of the response.
     */
    public function statusLine(): string;

    /**
     * The response time in milliseconds or -1 if no response time could be measured.
     */
    public function time(): int;
}
