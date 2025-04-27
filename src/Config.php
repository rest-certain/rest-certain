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

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message;
use Psr\Http\Message\UriInterface;
use RestCertain\Http\HttpFactory;
use Stringable;

use function assert;

/**
 * REST Certain configuration.
 */
final readonly class Config
{
    public const string DEFAULT_BASE_URI = 'http://localhost';
    public const string DEFAULT_BASE_PATH = '/';
    public const int DEFAULT_PORT = 8000;

    /**
     * The Base URI used by default in all requests.
     */
    public UriInterface $baseUri;

    /**
     * The HTTP client to use for sending requests.
     */
    public ClientInterface $httpClient;

    /**
     * The request factory to use for creating requests.
     */
    public Message\RequestFactoryInterface $requestFactory;

    /**
     * The response factory to use for creating responses.
     */
    public Message\ResponseFactoryInterface $responseFactory;

    /**
     * The stream factory to use for creating body content.
     */
    public Message\StreamFactoryInterface $streamFactory;

    /**
     * The URI factory to use for creating URIs.
     */
    public Message\UriFactoryInterface $uriFactory;

    /**
     * @param Stringable | UriInterface | string $baseUri The base URI that's used for all requests if a non-fully
     *     qualified URI is used in the request.
     * @param string $basePath A base path that's added to {@see self::$baseUri} on all requests using a non-fully
     *     qualified URI.
     * @param int $port The port that's used for all requests if a non-fully qualified URI is used in the request.
     */
    public function __construct(
        Stringable | UriInterface | string $baseUri = self::DEFAULT_BASE_URI,
        public string $basePath = self::DEFAULT_BASE_PATH,
        public int $port = self::DEFAULT_PORT,
        ?ClientInterface $httpClient = null,
        ?Message\RequestFactoryInterface $requestFactory = null,
        ?Message\ResponseFactoryInterface $responseFactory = null,
        ?Message\StreamFactoryInterface $streamFactory = null,
        ?Message\UriFactoryInterface $uriFactory = null,
    ) {
        $this->httpClient = $httpClient ?? $this->httpFactory();
        $this->requestFactory = $requestFactory ?? $this->httpFactory();
        $this->responseFactory = $responseFactory ?? $this->httpFactory();
        $this->streamFactory = $streamFactory ?? $this->httpFactory();
        $this->uriFactory = $uriFactory ?? $this->httpFactory();
        $this->baseUri = $baseUri instanceof UriInterface ? $baseUri : $this->uriFactory->createUri((string) $baseUri);
    }

    private function httpFactory(): HttpFactory
    {
        static $httpFactory = null;

        if ($httpFactory === null) {
            $httpFactory = new HttpFactory();
        }

        assert($httpFactory instanceof HttpFactory);

        return $httpFactory;
    }
}
