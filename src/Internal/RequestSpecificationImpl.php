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
use JsonSerializable;
use League\Uri\Components\Scheme;
use League\Uri\Modifier;
use League\Uri\UriTemplate\Template;
use Override;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RestCertain\Config;
use RestCertain\Exception\PendingRequest;
use RestCertain\Exception\RequestFailed;
use RestCertain\Exception\TooManyBodies;
use RestCertain\Http\Header;
use RestCertain\Http\MediaType;
use RestCertain\Http\Method;
use RestCertain\Request\Sender;
use RestCertain\Response\Response;
use RestCertain\Specification\RequestSpecification;
use RestCertain\Specification\ResponseSpecification;
use SplFileInfo;
use Stringable;
use stdClass;

use function array_map;
use function array_merge;
use function array_pop;
use function assert;
use function is_array;
use function is_string;
use function json_encode;
use function strtolower;
use function strtoupper;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const PHP_QUERY_RFC1738;

/**
 * @internal
 */
final class RequestSpecificationImpl implements RequestSpecification
{
    private string $basePath;
    private UriInterface $baseUri;
    private ?StreamInterface $body = null;
    private Cookies $cookies;

    /** @var array<string, list<string>> */
    private array $formParams = [];

    /** @var array<string, list<string>> */
    private array $headers = [];

    /** @var array<string, list<string>> */
    private array $params = [];

    /** @var array<string, string> */
    private array $pathParams = [];

    private int $port;

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
        $this->header(Header::ACCEPT, $contentType);

        return $this;
    }

    #[Override] public function and(): static
    {
        return $this;
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

    #[Override] public function body(
        JsonSerializable | SplFileInfo | StreamInterface | Stringable | stdClass | array | string $body,
    ): static {
        $this->body = match (true) {
            $body instanceof JsonSerializable, $body instanceof stdClass,
                is_array($body) => $this->buildBodyStreamForJson($body),
            $body instanceof SplFileInfo => $this->buildBodyStreamForSplFileInfo($body),
            $body instanceof StreamInterface => $this->buildBodyStreamForStreamInterface($body),
            default => $this->buildBodyStreamForString($body),
        };

        return $this;
    }

    #[Override] public function contentType(Stringable | string $contentType): static
    {
        $this->header(Header::CONTENT_TYPE, $contentType);

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
        return $this->applyPathParamsAndSendRequest(Method::DELETE, $path, $pathParams);
    }

    #[Override] public function expect(): ResponseSpecification
    {
        if (!isset($this->responseSpecification)) {
            throw new PendingRequest(
                'Cannot call expect() before sending a request or setting a response '
                . 'specification with setResponseSpecification(); to send a request, call any of the'
                . Sender::class . ' methods',
            );
        }

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
        return $this->applyPathParamsAndSendRequest(Method::GET, $path, $pathParams);
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
        return $this->applyPathParamsAndSendRequest(Method::HEAD, $path, $pathParams);
    }

    #[Override] public function header(
        string $name,
        Stringable | string $value,
        Stringable | string ...$additionalValues,
    ): static {
        $name = strtolower($name);
        $values = [$value, ...$additionalValues];

        if ($name === Header::COOKIE) {
            return $this->cookie($name, array_pop($values));
        }

        $headerValues = $this->headers[$name] ?? [];

        foreach ($values as $v) {
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
        return $this->applyPathParamsAndSendRequest(Method::OPTIONS, $path, $pathParams);
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
        return $this->applyPathParamsAndSendRequest(Method::PATCH, $path, $pathParams);
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
        return $this->applyPathParamsAndSendRequest(Method::POST, $path, $pathParams);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function put(Stringable | UriInterface | string $path, array $pathParams = []): Response
    {
        return $this->applyPathParamsAndSendRequest(Method::PUT, $path, $pathParams);
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

    /**
     * @inheritDoc
     */
    #[Override] public function request(
        Stringable | string $method,
        Stringable | UriInterface | string $path,
        array $pathParams = [],
    ): Response {
        return $this->applyPathParamsAndSendRequest($method, $path, $pathParams);
    }

    #[Override] public function response(): ResponseSpecification
    {
        if (!isset($this->responseSpecification)) {
            throw new PendingRequest(
                'Cannot call response() before sending a request or setting a response '
                . 'specification with setResponseSpecification(); to send a request, call any of the'
                . Sender::class . ' methods',
            );
        }

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
        if (!isset($this->responseSpecification)) {
            throw new PendingRequest(
                'Cannot call then() before sending a request or setting a response '
                . 'specification with setResponseSpecification(); to send a request, call any of the'
                . Sender::class . ' methods',
            );
        }

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
     * @param array<string, Stringable | int | string> $pathParams
     */
    private function applyPathParamsAndSendRequest(
        Stringable | string $method,
        Stringable | UriInterface | string $path,
        array $pathParams,
    ): Response {
        $method = strtoupper((string) $method);
        $psrRequest = $this->buildRequest($method, $this->buildUri($method, $path, $pathParams));

        try {
            $psrResponse = $this->config->httpClient->sendRequest($psrRequest);
        } catch (ClientExceptionInterface $e) {
            throw new RequestFailed(message: "The request failed: {$e->getMessage()}", previous: $e);
        }

        return new ResponseImpl($this, $psrResponse, $psrRequest);
    }

    /**
     * @param JsonSerializable | stdClass | mixed[] $body
     */
    private function buildBodyStreamForJson(JsonSerializable | stdClass | array $body): StreamInterface
    {
        if (!isset($this->headers[Header::CONTENT_TYPE])) {
            $this->contentType(MediaType::APPLICATION_JSON);
        }

        return $this->config->streamFactory->createStream(
            (string) json_encode(
                $body,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ),
        );
    }

    private function buildBodyStreamForSplFileInfo(SplFileInfo $body): StreamInterface
    {
        if (!isset($this->headers[Header::CONTENT_TYPE])) {
            $this->contentType(MediaType::APPLICATION_OCTET_STREAM);
        }

        $file = $body->openFile('r');
        $fileContents = '';

        while (!$file->eof()) {
            $contents = $file->current();
            assert(is_string($contents));
            $fileContents .= $contents;
            $file->next();
        }

        return $this->config->streamFactory->createStream($fileContents);
    }

    private function buildBodyStreamForStreamInterface(StreamInterface $body): StreamInterface
    {
        if (!isset($this->headers[Header::CONTENT_TYPE])) {
            $this->contentType(MediaType::APPLICATION_OCTET_STREAM);
        }

        return $body;
    }

    private function buildBodyStreamForString(Stringable | string $body): StreamInterface
    {
        if (!isset($this->headers[Header::CONTENT_TYPE])) {
            $this->contentType(MediaType::TEXT_PLAIN);
        }

        return $this->config->streamFactory->createStream((string) $body);
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

    /**
     * @param array<string, Stringable | int | string> $pathParams
     *
     * @return array<string, string>
     */
    private function buildPathParameters(array $pathParams): array
    {
        return array_merge($this->pathParams, array_map(fn ($v) => (string) $v, $pathParams));
    }

    private function buildRequest(string $method, UriInterface $uri): RequestInterface
    {
        $request = $this->config->requestFactory->createRequest($method, $uri);

        foreach ($this->headers as $name => $values) {
            $request = $request->withHeader($name, $values);
        }

        if ($this->cookies->getAll() !== []) {
            $request = $this->cookies->renderIntoCookieHeader($request);
        }

        return $this->maybeAttachRequestBody($method, $request);
    }

    /**
     * @param array<string, Stringable | int | string> $pathParams
     */
    private function buildUri(string $method, Stringable | UriInterface | string $path, array $pathParams): UriInterface
    {
        $expandedPath = Template::new($path)->expandOrFail($this->buildPathParameters($pathParams));

        if (Scheme::fromUri($expandedPath)->value() !== null) {
            // We have a full URI, so we can just use it.
            $uriModifier = Modifier::from($expandedPath, $this->config->uriFactory);
        } else {
            // We need to build a URI from the base URI and the expanded path.
            $uri = $this->baseUri->withPath($this->basePath);
            if ($this->port !== 80 && ($this->port !== 443 || $uri->getScheme() !== 'https')) {
                $uri = $uri->withPort($this->port);
            }
            $uriModifier = Modifier::from($uri, $this->config->uriFactory)->appendSegment($expandedPath);
        }

        $baseParams = $method !== Method::POST ? $this->params : [];
        foreach ([...$baseParams, ...$this->queryParams] as $name => $values) {
            foreach ($values as $value) {
                $uriModifier = $uriModifier->appendQueryParameters([$name => $value]);
            }
        }

        return $this->config->uriFactory->createUri((string) $uriModifier->getUri());
    }

    private function buildUrlencodedFormData(string $method): ?string
    {
        $baseParams = $method === Method::POST ? $this->params : [];
        $formParams = [...$baseParams, ...$this->formParams];

        if ($formParams === []) {
            return null;
        }

        $formData = Modifier::from('', $this->config->uriFactory);

        foreach ($formParams as $name => $values) {
            foreach ($values as $value) {
                $formData = $formData->appendQueryParameters([$name => $value]);
            }
        }

        return $formData->encodeQuery(PHP_QUERY_RFC1738)->getUri()->getQuery();
    }

    private function maybeAttachRequestBody(string $method, RequestInterface $request): RequestInterface
    {
        $formData = $this->buildUrlencodedFormData($method);

        if ($formData !== null && $this->body !== null) {
            throw new TooManyBodies('Cannot set both body and form data');
        }

        if ($formData !== null) {
            $request = $request->withBody($this->config->streamFactory->createStream($formData));

            // If the user has already set a content-type, use it.
            // Otherwise, use the default for HTML form data.
            if (!$request->hasHeader(Header::CONTENT_TYPE)) {
                $request = $request->withHeader(Header::CONTENT_TYPE, MediaType::APPLICATION_X_WWW_FORM_URLENCODED);
            }
        } elseif ($this->body !== null) {
            $request = $request->withBody($this->body);
        }

        return $request;
    }
}
