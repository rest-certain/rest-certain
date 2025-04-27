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

use Psr\Http\Message\UriInterface;
use RestCertain\Exception\RequestFailed;
use RestCertain\Response\Response;
use Stringable;

interface RequestSender
{
    /**
     * Performs a DELETE request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     *
     * @throws RequestFailed
     */
    public function delete(Stringable | UriInterface | string $path, array $pathParams = []): Response;

    /**
     * Performs a GET request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     *
     * @throws RequestFailed
     */
    public function get(Stringable | UriInterface | string $path, array $pathParams = []): Response;

    /**
     * Performs a HEAD request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     *
     * @throws RequestFailed
     */
    public function head(Stringable | UriInterface | string $path, array $pathParams = []): Response;

    /**
     * Performs an OPTIONS request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     *
     * @throws RequestFailed
     */
    public function options(Stringable | UriInterface | string $path, array $pathParams = []): Response;

    /**
     * Performs a PATCH request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     *
     * @throws RequestFailed
     */
    public function patch(Stringable | UriInterface | string $path, array $pathParams = []): Response;

    /**
     * Performs a POST request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     *
     * @throws RequestFailed
     */
    public function post(Stringable | UriInterface | string $path, array $pathParams = []): Response;

    /**
     * Performs a PUT request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     *
     * @throws RequestFailed
     */
    public function put(Stringable | UriInterface | string $path, array $pathParams = []): Response;

    /**
     * Performs an HTTP request to the given path using the given method.
     *
     * @param array<string, Stringable | int | string> $pathParams
     *
     * @throws RequestFailed
     */
    public function request(
        Stringable | string $method,
        Stringable | UriInterface | string $path,
        array $pathParams = [],
    ): Response;
}
