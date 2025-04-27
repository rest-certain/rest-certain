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

namespace RestCertain;

use Psr\Http\Message\UriInterface;
use RestCertain\Response\Response;
use RestCertain\Specification\RequestSpecification;
use Stringable;

// phpcs:disable Squiz.Functions.GlobalFunction.Found

/**
 * Performs a DELETE request to the given path.
 *
 * @param array<string, Stringable | int | string> $pathParams
 */
function delete(Stringable | UriInterface | string $path, array $pathParams = []): Response
{
    return RestCertain::delete($path, $pathParams);
}

/**
 * Performs a GET request to the given path.
 *
 * @param array<string, Stringable | int | string> $pathParams
 */
function get(Stringable | UriInterface | string $path, array $pathParams = []): Response
{
    return RestCertain::get($path, $pathParams);
}

/**
 * Begins a request specification.
 */
function given(): RequestSpecification
{
    return RestCertain::given();
}

/**
 * Performs a HEAD request to the given path.
 *
 * @param array<string, Stringable | int | string> $pathParams
 */
function head(Stringable | UriInterface | string $path, array $pathParams = []): Response
{
    return RestCertain::head($path, $pathParams);
}

/**
 * Performs an OPTIONS request to the given path.
 *
 * @param array<string, Stringable | int | string> $pathParams
 */
function options(Stringable | UriInterface | string $path, array $pathParams = []): Response
{
    return RestCertain::options($path, $pathParams);
}

/**
 * Performs a PATCH request to the given path.
 *
 * @param array<string, Stringable | int | string> $pathParams
 */
function patch(Stringable | UriInterface | string $path, array $pathParams = []): Response
{
    return RestCertain::patch($path, $pathParams);
}

/**
 * Performs a POST request to the given path.
 *
 * @param array<string, Stringable | int | string> $pathParams
 */
function post(Stringable | UriInterface | string $path, array $pathParams = []): Response
{
    return RestCertain::post($path, $pathParams);
}

/**
 * Performs a PUT request to the given path.
 *
 * @param array<string, Stringable | int | string> $pathParams
 */
function put(Stringable | UriInterface | string $path, array $pathParams = []): Response
{
    return RestCertain::put($path, $pathParams);
}

/**
 * Performs an HTTP request to the given path using the given method.
 *
 * @param array<string, Stringable | int | string> $pathParams
 */
function request(
    Stringable | string $method,
    Stringable | UriInterface | string $path,
    array $pathParams = [],
): Response {
    return RestCertain::request($method, $path, $pathParams);
}

/**
 * Begins a request specification.
 */
function when(): RequestSpecification
{
    return RestCertain::when();
}

/**
 * Begins a request specification.
 */
function with(): RequestSpecification
{
    return RestCertain::with();
}
