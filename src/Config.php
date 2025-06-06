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
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use RestCertain\Http\HttpFactory;
use RestCertain\Json\Schema\Config as JsonSchemaConfig;
use Stringable;

/**
 * REST Certain configuration.
 */
final class Config
{
    public const string DEFAULT_BASE_URI = 'http://localhost';
    public const string DEFAULT_BASE_PATH = '/';
    public const int DEFAULT_PORT = 8000;

    /**
     * The Base URI used by default in all requests.
     */
    public readonly UriInterface $baseUri;

    /**
     * The HTTP client to use for sending requests.
     */
    public readonly ClientInterface $httpClient;

    /**
     * The request factory to use for creating requests.
     */
    public readonly RequestFactoryInterface $requestFactory;

    /**
     * The response factory to use for creating responses.
     */
    public readonly ResponseFactoryInterface $responseFactory;

    /**
     * The stream factory to use for creating body content.
     */
    public readonly StreamFactoryInterface $streamFactory;

    /**
     * The URI factory to use for creating URIs.
     */
    public readonly UriFactoryInterface $uriFactory;

    /**
     * The JSON Schema configuration to use for requesting and handling JSON Schemas.
     */
    public readonly JsonSchemaConfig $jsonSchemaConfig;

    /**
     * A default HTTP factory to use for creating HTTP components.
     *
     * If all components are provided via the constructor, then this remains `null`.
     */
    private ?HttpFactory $httpFactory = null;

    /**
     * @param Stringable | UriInterface | string $baseUri The base URI that's used for all requests if a non-fully
     *     qualified URI is used in the request.
     * @param string $basePath A base path that's added to {@see self::$baseUri} on all requests using a non-fully
     *     qualified URI.
     * @param int $port The port that's used for all requests if a non-fully qualified URI is used in the request.
     */
    public function __construct(
        Stringable | UriInterface | string $baseUri = self::DEFAULT_BASE_URI,
        public readonly string $basePath = self::DEFAULT_BASE_PATH,
        public readonly int $port = self::DEFAULT_PORT,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UriFactoryInterface $uriFactory = null,
        ?JsonSchemaConfig $jsonSchemaConfig = null,
    ) {
        $this->httpClient = $httpClient ?? $this->httpFactory();
        $this->requestFactory = $requestFactory ?? $this->httpFactory();
        $this->responseFactory = $responseFactory ?? $this->httpFactory();
        $this->streamFactory = $streamFactory ?? $this->httpFactory();
        $this->uriFactory = $uriFactory ?? $this->httpFactory();
        $this->baseUri = $baseUri instanceof UriInterface ? $baseUri : $this->uriFactory->createUri((string) $baseUri);
        $this->jsonSchemaConfig = $jsonSchemaConfig ?? new JsonSchemaConfig($this);
    }

    /**
     * Returns a new instance of Config with the given base URI.
     *
     * This clones all config properties, creating new instances that no longer
     * hold references to the original config properties.
     */
    public function withBaseUri(Stringable | UriInterface | string $baseUri): self
    {
        return new self(...['baseUri' => $baseUri] + $this->copyProperties());
    }

    /**
     * Returns a new instance of Config with the given base path.
     *
     * This clones all config properties, creating new instances that no longer
     * hold references to the original config properties.
     */
    public function withBasePath(string $basePath): self
    {
        return new self(...['basePath' => $basePath] + $this->copyProperties());
    }

    /**
     * Returns a new instance of Config with the given port.
     *
     * This clones all config properties, creating new instances that no longer
     * hold references to the original config properties.
     */
    public function withPort(int $port): self
    {
        return new self(...['port' => $port] + $this->copyProperties());
    }

    /**
     * Returns a new instance of Config with the given HTTP client.
     *
     * This clones all config properties, creating new instances that no longer
     * hold references to the original config properties.
     */
    public function withHttpClient(ClientInterface $httpClient): self
    {
        return new self(...['httpClient' => $httpClient] + $this->copyProperties());
    }

    /**
     * Returns a new instance of Config with the given request factory.
     *
     * This clones all config properties, creating new instances that no longer
     * hold references to the original config properties.
     */
    public function withRequestFactory(RequestFactoryInterface $requestFactory): self
    {
        return new self(...['requestFactory' => $requestFactory] + $this->copyProperties());
    }

    /**
     * Returns a new instance of Config with the given response factory.
     *
     * This clones all config properties, creating new instances that no longer
     * hold references to the original config properties.
     */
    public function withResponseFactory(ResponseFactoryInterface $responseFactory): self
    {
        return new self(...['responseFactory' => $responseFactory] + $this->copyProperties());
    }

    /**
     * Returns a new instance of Config with the given stream factory.
     *
     * This clones all config properties, creating new instances that no longer
     * hold references to the original config properties.
     */
    public function withStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        return new self(...['streamFactory' => $streamFactory] + $this->copyProperties());
    }

    /**
     * Returns a new instance of Config with the given URI factory.
     *
     * This clones all config properties, creating new instances that no longer
     * hold references to the original config properties.
     */
    public function withUriFactory(UriFactoryInterface $uriFactory): self
    {
        return new self(...['uriFactory' => $uriFactory] + $this->copyProperties());
    }

    /**
     * Returns a new instance of Config with the given JSON Schema configuration.
     *
     * This clones all config properties, creating new instances that no longer
     * hold references to the original config properties.
     */
    public function withJsonSchemaConfig(JsonSchemaConfig $jsonSchemaConfig): self
    {
        return new self(...['jsonSchemaConfig' => $jsonSchemaConfig] + $this->copyProperties());
    }

    /**
     * @return array{
     *     baseUri: UriInterface,
     *     basePath: string,
     *     port: int,
     *     httpClient: ClientInterface,
     *     requestFactory: RequestFactoryInterface,
     *     responseFactory: ResponseFactoryInterface,
     *     streamFactory: StreamFactoryInterface,
     *     uriFactory: UriFactoryInterface,
     *     jsonSchemaConfig: JsonSchemaConfig,
     * }
     */
    private function copyProperties(): array
    {
        return [
            'baseUri' => clone $this->baseUri,
            'basePath' => $this->basePath,
            'port' => $this->port,
            'httpClient' => clone $this->httpClient,
            'requestFactory' => clone $this->requestFactory,
            'responseFactory' => clone $this->responseFactory,
            'streamFactory' => clone $this->streamFactory,
            'uriFactory' => clone $this->uriFactory,
            'jsonSchemaConfig' => clone $this->jsonSchemaConfig,
        ];
    }

    private function httpFactory(): HttpFactory
    {
        if ($this->httpFactory === null) {
            $this->httpFactory = new HttpFactory();
        }

        return $this->httpFactory;
    }
}
