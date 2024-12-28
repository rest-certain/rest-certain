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

namespace RestCertain\Http;

use Http\Discovery\Psr18Client;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message;

use const UPLOAD_ERR_OK;

/**
 * @internal
 */
final readonly class HttpFactory implements
    ClientInterface,
    Message\RequestFactoryInterface,
    Message\ResponseFactoryInterface,
    Message\ServerRequestFactoryInterface,
    Message\StreamFactoryInterface,
    Message\UploadedFileFactoryInterface,
    Message\UriFactoryInterface
{
    private ClientInterface $httpClient;
    private Psr18Client $httpFactory;

    public function __construct(?ClientInterface $httpClient = null)
    {
        $this->httpFactory = new Psr18Client();
        $this->httpClient = $httpClient ?? $this->httpFactory;
    }

    /**
     * @param Message\UriInterface | string $uri
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function createRequest(string $method, $uri): Message\RequestInterface
    {
        return $this->httpFactory->createRequest($method, $uri);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): Message\ResponseInterface
    {
        return $this->httpFactory->createResponse($code, $reasonPhrase);
    }

    /**
     * @param Message\UriInterface | string $uri
     * @param array<string, string | int | float | bool | null> $serverParams
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function createServerRequest(string $method, $uri, array $serverParams = []): Message\ServerRequestInterface
    {
        return $this->httpFactory->createServerRequest($method, $uri, $serverParams);
    }

    public function createStream(string $content = ''): Message\StreamInterface
    {
        return $this->httpFactory->createStream($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): Message\StreamInterface
    {
        return $this->httpFactory->createStreamFromFile($filename, $mode);
    }

    /**
     * @param resource $resource
     */
    public function createStreamFromResource($resource): Message\StreamInterface
    {
        return $this->httpFactory->createStreamFromResource($resource);
    }

    public function createUploadedFile(
        Message\StreamInterface $stream,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null,
    ): Message\UploadedFileInterface {
        return $this->httpFactory->createUploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    public function createUri(string $uri = ''): Message\UriInterface
    {
        return $this->httpFactory->createUri($uri);
    }

    public function sendRequest(Message\RequestInterface $request): Message\ResponseInterface
    {
        return $this->httpClient->sendRequest($request);
    }
}
