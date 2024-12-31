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

namespace RestCertain\Internal;

use JsonException;
use JsonSerializable;
use Override;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RestCertain\Config;
use RestCertain\Response\Response;
use RestCertain\Specification\AuthenticationSpecification;
use RestCertain\Specification\RedirectSpecification;
use RestCertain\Specification\RequestSpecification;
use RestCertain\Specification\ResponseSpecification;
use SplFileInfo;
use Stringable;

use function array_merge;
use function array_unshift;
use function count;
use function is_array;
use function json_encode;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @internal
 */
final class RequestSpecificationImpl implements RequestSpecification
{
    private ?string $accept = null;
    private string $basePath;
    private UriInterface $baseUri;
    private ?StreamInterface $body = null;
    private ?string $contentType = null;

    /** @var array<string, string[]> */
    private array $cookies = [];

    /** @var array<string, string | string[]> */
    private array $formParams = [];

    /** @var array<string, string[]> */
    private array $headers = [];

    /** @var array<string, string | string[]> */
    private array $params = [];

    /** @var array<string, string> */
    private array $pathParams = [];

    private int $port;

    /** @var array<string, string | string[]> */
    private array $queryParams = [];

    private ResponseSpecification $responseSpecification;

    public function __construct(public readonly Config $config)
    {
        $this->basePath = $this->config->basePath;
        $this->baseUri = $this->config->baseUri;
        $this->port = $this->config->port;
    }

    #[Override] public function accept(Stringable | string $contentType): static
    {
        $this->accept = (string) $contentType;

        return $this;
    }

    #[Override] public function and(): static
    {
        return $this;
    }

    #[Override] public function auth(): AuthenticationSpecification
    {
        return new AuthenticationSpecificationImpl($this);
    }

    #[Override] public function basePath(Stringable | string $basePath): static
    {
        $this->basePath = (string) $basePath;

        return $this;
    }

    #[Override] public function baseUri(Stringable | UriInterface | string $baseUri): static
    {
        $this->baseUri = $baseUri instanceof UriInterface
            ? $baseUri
            : $this->config->uriFactory->createUri((string) $baseUri);

        return $this;
    }

    /**
     * @throws JsonException
     */
    #[Override] public function body(
        JsonSerializable | SplFileInfo | StreamInterface | Stringable | string $body,
    ): static {
        $this->body = match (true) {
            $body instanceof JsonSerializable => $this->config->streamFactory->createStream(
                (string) json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ),
            $body instanceof SplFileInfo => $this->config->streamFactory->createStreamFromFile($body->getPathname()),
            $body instanceof StreamInterface => $body,
            default => $this->config->streamFactory->createStream((string) $body),
        };

        return $this;
    }

    #[Override] public function contentType(Stringable | string $contentType): static
    {
        $this->contentType = (string) $contentType;

        return $this;
    }

    #[Override] public function cookie(
        string $name,
        Stringable | string $value = '',
        Stringable | string ...$additionalValues,
    ): static {
        array_unshift($additionalValues, $value);

        if (!isset($this->cookies[$name])) {
            $this->cookies[$name] = [];
        }

        foreach ($additionalValues as $v) {
            $this->cookies[$name][] = (string) $v;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function cookies(array $cookies): static
    {
        foreach ($cookies as $name => $value) {
            if (is_array($value)) {
                $this->cookie($name, ...$value);
            } else {
                $this->cookie($name, $value);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function delete(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        // TODO: Implement delete() method.
    }

    #[Override] public function expect(): ResponseSpecification
    {
        return $this->responseSpecification;
    }

    #[Override] public function formParam(
        string $name,
        Stringable | array | string $value = '',
        Stringable | string ...$additionalValues,
    ): static {
        $this->formParams[$name] = $this->buildParameterValue(
            $value,
            $additionalValues,
            $this->formParams[$name] ?? [],
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function formParams(array $parameters): static
    {
        foreach ($parameters as $name => $value) {
            $this->formParam($name, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function get(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        // TODO: Implement get() method.
    }

    #[Override] public function given(): static
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function head(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        // TODO: Implement head() method.
    }

    #[Override] public function header(
        string $name,
        Stringable | string $value,
        Stringable | string ...$additionalValues,
    ): static {
        $existingValue = $this->headers[$name] ?? [];
        $headerValues = [];

        /** @var Stringable | string $v */
        foreach (array_merge((array) $value, $additionalValues) as $v) {
            $headerValues[] = (string) $v;
        }

        $this->headers[$name] = array_merge($existingValue, $headerValues);

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function headers(array $headers): static
    {
        foreach ($headers as $name => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $this->header($name, ...$value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function options(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        // TODO: Implement options() method.
    }

    #[Override] public function param(
        string $name,
        Stringable | array | string $value,
        Stringable | string ...$additionalValues,
    ): static {
        $this->params[$name] = $this->buildParameterValue(
            $value,
            $additionalValues,
            $this->params[$name] ?? [],
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function params(array $parameters): static
    {
        foreach ($parameters as $name => $value) {
            $this->param($name, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function patch(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        // TODO: Implement patch() method.
    }

    #[Override] public function pathParam(string $name, Stringable | int | string $value): static
    {
        $this->pathParams[$name] = (string) $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function pathParams(array $parameters): static
    {
        foreach ($parameters as $name => $value) {
            $this->pathParam($name, $value);
        }

        return $this;
    }

    #[Override] public function port(int $port): static
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function post(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        // TODO: Implement post() method.
    }

    /**
     * @inheritDoc
     */
    #[Override] public function put(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        // TODO: Implement put() method.
    }

    #[Override] public function queryParam(
        string $name,
        Stringable | array | string $value,
        Stringable | string ...$additionalValues,
    ): static {
        $this->queryParams[$name] = $this->buildParameterValue(
            $value,
            $additionalValues,
            $this->queryParams[$name] ?? [],
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function queryParams(array $parameters): static
    {
        foreach ($parameters as $name => $value) {
            $this->queryParam($name, $value);
        }

        return $this;
    }

    #[Override] public function redirects(): RedirectSpecification
    {
        return new RedirectSpecificationImpl($this, $this->config->httpClient);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function request(
        Stringable | string $method,
        Stringable | UriInterface | string $path,
        array $pathParams = [],
    ): Response {
        // TODO: Implement request() method.
    }

    #[Override] public function response(): ResponseSpecification
    {
        return $this->responseSpecification;
    }

    #[Override] public function setResponseSpecification(ResponseSpecification $responseSpecification): static
    {
        $this->responseSpecification = $responseSpecification;

        return $this;
    }

    #[Override] public function that(): static
    {
        return $this;
    }

    #[Override] public function then(): ResponseSpecification
    {
        return $this->responseSpecification;
    }

    #[Override] public function when(): static
    {
        return $this;
    }

    #[Override] public function with(): static
    {
        return $this;
    }

    /**
     * @param Stringable | string | array<Stringable | string> $value
     * @param array<Stringable | string> $additionalValues
     * @param string | array<string> $existingValue
     *
     * @return string | list<string>
     */
    private function buildParameterValue(
        Stringable | array | string $value,
        array $additionalValues = [],
        array | string $existingValue = [],
    ): array | string {
        $newValue = [];

        /** @var Stringable | string $v */
        foreach (array_merge((array) $existingValue, (array) $value, $additionalValues) as $v) {
            $newValue[] = (string) $v;
        }

        if (count($newValue) === 1 && !is_array($value)) {
            $newValue = $newValue[0];
        }

        return $newValue;
    }
}
