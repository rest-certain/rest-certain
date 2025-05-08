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

use Dflydev\FigCookies\SetCookies;
use Override;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Http\Header;
use RestCertain\Response\Response;
use RestCertain\Response\ResponseBody;
use RestCertain\Response\ValidatableResponse;
use RestCertain\Specification\RequestSpecification;

/**
 * @internal This class is not intended for direct use outside of Rest Certain.
 */
final readonly class HttpResponse implements Response
{
    private ResponseBody $body;
    private ResponseInterface $psrResponse;
    private RequestInterface $psrRequest;
    private SetCookies $setCookies;
    private ValidatableResponse $validatableResponse;

    /**
     * @param int $time The time in milliseconds that the request took to complete, or -1 if the time is not known.
     */
    public function __construct(
        private RequestSpecification $requestSpecification,
        ResponseInterface $response,
        RequestInterface $request,
        private int $time = -1,
    ) {
        $this->psrResponse = $response;
        $this->psrRequest = $request;
        $this->body = new HttpResponseBody($this->psrResponse);
        $this->setCookies = SetCookies::fromResponse($this->psrResponse);

        $responseSpec = new ResponseExpectations($this);
        $this->requestSpecification->setResponseSpecification($responseSpec);
        $this->validatableResponse = new ResponseValidator($responseSpec);
    }

    #[Override] public function andReturn(): static
    {
        return $this;
    }

    #[Override] public function asPrettyString(): string
    {
        return $this->body->asPrettyString();
    }

    #[Override] public function asString(): string
    {
        return $this->body->asString();
    }

    #[Override] public function body(): ResponseBody & StreamInterface
    {
        return $this->body;
    }

    #[Override] public function contentType(): ?string
    {
        return $this->getContentType();
    }

    #[Override] public function cookie(string $name): ?string
    {
        return $this->getCookie($name);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function cookies(): array
    {
        return $this->getCookies();
    }

    #[Override] public function getBody(): ResponseBody & StreamInterface
    {
        return $this->body;
    }

    #[Override] public function getContentType(): ?string
    {
        if (!$this->psrResponse->hasHeader(Header::CONTENT_TYPE)) {
            return null;
        }

        return $this->psrResponse->getHeaderLine(Header::CONTENT_TYPE);
    }

    #[Override] public function getCookie(string $name): ?string
    {
        return $this->setCookies->get($name)?->getValue();
    }

    /**
     * @inheritDoc
     */
    #[Override] public function getCookies(): array
    {
        $cookies = [];
        foreach ($this->setCookies->getAll() as $setCookie) {
            $cookies[$setCookie->getName()] = (string) $setCookie->getValue();
        }

        return $cookies;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function getHeader(string $name): array
    {
        /** @var list<string> */
        return $this->psrResponse->getHeader($name);
    }

    #[Override] public function getHeaderLine(string $name): string
    {
        return $this->psrResponse->getHeaderLine($name);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function getHeaders(): array
    {
        /** @var array<string, list<string>> */
        return $this->psrResponse->getHeaders();
    }

    #[Override] public function getProtocolVersion(): string
    {
        return $this->psrResponse->getProtocolVersion();
    }

    /**
     * @internal
     */
    public function getPsrRequest(): RequestInterface
    {
        return $this->psrRequest;
    }

    /**
     * @internal
     */
    public function getPsrResponse(): ResponseInterface
    {
        return $this->psrResponse;
    }

    #[Override] public function getReasonPhrase(): string
    {
        return $this->psrResponse->getReasonPhrase();
    }

    #[Override] public function getStatusCode(): int
    {
        return $this->psrResponse->getStatusCode();
    }

    #[Override] public function getStatusLine(): string
    {
        $protocol = $this->psrResponse->getProtocolVersion();
        $code = $this->psrResponse->getStatusCode();
        $reasonPhrase = $this->psrResponse->getReasonPhrase();

        return "HTTP/$protocol $code $reasonPhrase";
    }

    #[Override] public function getTime(): int
    {
        return $this->time;
    }

    #[Override] public function hasHeader(string $name): bool
    {
        return $this->psrResponse->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function header(string $name): array
    {
        /** @var list<string> */
        return $this->psrResponse->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function headers(): array
    {
        /** @var array<string, list<string>> */
        return $this->psrResponse->getHeaders();
    }

    #[Override] public function path(string $path): mixed
    {
        return $this->body->path($path);
    }

    #[Override] public function prettyPrint(): string
    {
        return $this->body->prettyPrint();
    }

    #[Override] public function print(): string
    {
        return $this->body->print();
    }

    #[Override] public function statusCode(): int
    {
        return $this->psrResponse->getStatusCode();
    }

    #[Override] public function statusLine(): string
    {
        return $this->getStatusLine();
    }

    #[Override] public function then(): ValidatableResponse
    {
        return $this->validatableResponse;
    }

    #[Override] public function thenReturn(): static
    {
        return $this;
    }

    #[Override] public function time(): int
    {
        return $this->time;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function withAddedHeader(string $name, $value): Response
    {
        return new HttpResponse(
            $this->requestSpecification,
            $this->psrResponse->withAddedHeader($name, $value),
            $this->psrRequest,
        );
    }

    #[Override] public function withBody(StreamInterface $body): Response
    {
        return new HttpResponse(
            $this->requestSpecification,
            $this->psrResponse->withBody($body),
            $this->psrRequest,
        );
    }

    /**
     * @inheritDoc
     */
    #[Override] public function withHeader(string $name, $value): Response
    {
        return new HttpResponse(
            $this->requestSpecification,
            $this->psrResponse->withHeader($name, $value),
            $this->psrRequest,
        );
    }

    #[Override] public function withProtocolVersion(string $version): Response
    {
        return new HttpResponse(
            $this->requestSpecification,
            $this->psrResponse->withProtocolVersion($version),
            $this->psrRequest,
        );
    }

    #[Override] public function withStatus(int $code, string $reasonPhrase = ''): Response
    {
        return new HttpResponse(
            $this->requestSpecification,
            $this->psrResponse->withStatus($code, $reasonPhrase),
            $this->psrRequest,
        );
    }

    #[Override] public function withoutHeader(string $name): Response
    {
        return new HttpResponse(
            $this->requestSpecification,
            $this->psrResponse->withoutHeader($name),
            $this->psrRequest,
        );
    }
}
