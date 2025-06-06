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
use RestCertain\Internal\RequestBuilder;
use RestCertain\Response\Response;
use RestCertain\Specification\RequestSpecification;
use Stringable;

/**
 * REST Certain is a PHP-based DSL (domain-specific language) for testing REST APIs.
 */
final class RestCertain
{
    /**
     * Default user agent string for REST Certain requests.
     */
    public const string USER_AGENT = 'RESTCertain (https://github.com/rest-certain/rest-certain)';

    /**
     * REST Certain configuration.
     */
    public static ?Config $config = null;

    /**
     * Performs a DELETE request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     */
    public static function delete(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return self::newRequestSpec()->delete($path, $pathParams);
    }

    /**
     * Performs a GET request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     */
    public static function get(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return self::newRequestSpec()->get($path, $pathParams);
    }

    /**
     * Begins a request specification.
     */
    public static function given(): RequestSpecification
    {
        return self::newRequestSpec();
    }

    /**
     * Performs a HEAD request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     */
    public static function head(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return self::newRequestSpec()->head($path, $pathParams);
    }

    /**
     * Performs an OPTIONS request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     */
    public static function options(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return self::newRequestSpec()->options($path, $pathParams);
    }

    /**
     * Performs a PATCH request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     */
    public static function patch(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return self::newRequestSpec()->patch($path, $pathParams);
    }

    /**
     * Performs a POST request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     */
    public static function post(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return self::newRequestSpec()->post($path, $pathParams);
    }

    /**
     * Performs a PUT request to the given path.
     *
     * @param array<string, Stringable | int | string> $pathParams
     */
    public static function put(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return self::newRequestSpec()->put($path, $pathParams);
    }

    /**
     * Performs an HTTP request to the given path using the given method.
     *
     * @param array<string, Stringable | int | string> $pathParams
     */
    public static function request(
        Stringable | string $method,
        Stringable | UriInterface | string $path,
        array $pathParams = [],
    ): Response {
        return self::newRequestSpec()->request($method, $path, $pathParams);
    }

    /**
     * Begins a request specification.
     */
    public static function when(): RequestSpecification
    {
        return self::newRequestSpec();
    }

    /**
     * Begins a request specification.
     */
    public static function with(): RequestSpecification
    {
        return self::newRequestSpec();
    }

    private static function newRequestSpec(): RequestSpecification
    {
        if (self::$config !== null) {
            return new RequestBuilder(self::$config);
        }

        // phpcs:disable SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable
        /** @var string $baseUri */
        $baseUri = $_ENV['REST_CERTAIN_BASE_URI'] ?? Config::DEFAULT_BASE_URI;
        /** @var string $basePath */
        $basePath = $_ENV['REST_CERTAIN_BASE_PATH'] ?? Config::DEFAULT_BASE_PATH;
        /** @var int | string $port */
        $port = $_ENV['REST_CERTAIN_PORT'] ?? Config::DEFAULT_PORT;
        // phpcs:enable

        self::$config = new Config(
            baseUri: $baseUri,
            basePath: $basePath,
            port: (int) $port,
        );

        return new RequestBuilder(self::$config);
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
