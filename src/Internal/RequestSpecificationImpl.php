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

use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\Cookies;
use JsonException;
use JsonSerializable;
use LogicException;
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

use function is_array;
use function json_encode;
use function strtolower;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @internal
 */
final class RequestSpecificationImpl implements RequestSpecification
{
    private ?string $accept = null; // @phpstan-ignore property.onlyWritten
    private string $basePath; // @phpstan-ignore property.onlyWritten
    private UriInterface $baseUri; // @phpstan-ignore property.onlyWritten
    private ?StreamInterface $body = null; // @phpstan-ignore property.onlyWritten
    private ?string $contentType = null; // @phpstan-ignore property.onlyWritten
    private Cookies $cookies;

    /** @var array<string, list<string>> */
    private array $formParams = [];

    /** @var array<string, list<string>> */
    private array $headers = [];

    /** @var array<string, list<string>> */
    private array $params = [];

    /** @var array<string, string> */
    private array $pathParams = []; // @phpstan-ignore property.onlyWritten

    private int $port; // @phpstan-ignore property.onlyWritten

    /** @var array<string, list<string>> */
    private array $queryParams = [];

    private ResponseSpecification $responseSpecification;

    public function __construct(public readonly Config $config)
    {
        $this->basePath = $this->config->basePath;
        $this->baseUri = $this->config->baseUri;
        $this->port = $this->config->port;
        $this->cookies = new Cookies();
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

    #[Override] public function cookie(string $name, Stringable | string | null $value = null): static
    {
        $this->cookies = $this->cookies->with(new Cookie($name, $value !== null ? (string) $value : null));

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function cookies(array $cookies): static
    {
        foreach ($cookies as $name => $value) {
            $this->cookie($name, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function delete(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function expect(): ResponseSpecification
    {
        return $this->responseSpecification;
    }

    #[Override] public function formParam(
        string $name,
        Stringable | string $value,
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
            if (!is_array($value)) {
                $value = [$value];
            }
            $this->formParam($name, ...$value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function get(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        throw new LogicException('Not implemented yet');
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
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function header(
        string $name,
        Stringable | string $value,
        Stringable | string ...$additionalValues,
    ): static {
        $name = strtolower($name);
        $headerValues = $this->headers[$name] ?? [];

        foreach ([$value, ...$additionalValues] as $v) {
            $headerValues[] = (string) $v;
        }

        $this->headers[$name] = $headerValues;

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
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function param(
        string $name,
        Stringable | string $value,
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
            if (!is_array($value)) {
                $value = [$value];
            }
            $this->param($name, ...$value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function patch(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        throw new LogicException('Not implemented yet');
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
        throw new LogicException('Not implemented yet');
    }

    /**
     * @inheritDoc
     */
    #[Override] public function put(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function queryParam(
        string $name,
        Stringable | string $value,
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
            if (!is_array($value)) {
                $value = [$value];
            }
            $this->queryParam($name, ...$value);
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
        throw new LogicException('Not implemented yet');
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
     * @param array<Stringable | string> $additionalValues
     * @param list<string> $existingValue
     *
     * @return list<string>
     */
    private function buildParameterValue(
        Stringable | string $value,
        array $additionalValues = [],
        array $existingValue = [],
    ): array {
        $newValue = $existingValue;

        foreach ([$value, ...$additionalValues] as $v) {
            $newValue[] = (string) $v;
        }

        return $newValue;
    }
}
