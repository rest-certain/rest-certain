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
use LogicException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Http\Header;
use RestCertain\Response\Response;
use RestCertain\Response\ResponseBody;
use RestCertain\Response\ValidatableResponseOptions;

/**
 * @internal
 */
final readonly class ResponseImpl implements Response
{
    private ResponseBody $body;
    private SetCookies $setCookies;
    private ValidatableResponseOptions $validatableResponseOptions;

    public function __construct(private ResponseInterface $response)
    {
        $this->body = new ResponseBodyImpl($this->response);
        $this->setCookies = SetCookies::fromResponse($this->response);

        $responseSpec = new ResponseSpecificationImpl($this);
        $this->validatableResponseOptions = new ValidatableResponseOptionsImpl($responseSpec);
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
        if (!$this->response->hasHeader(Header::CONTENT_TYPE)) {
            return null;
        }

        return $this->response->getHeaderLine(Header::CONTENT_TYPE);
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
        return $this->response->getHeader($name);
    }

    #[Override] public function getHeaderLine(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function getHeaders(): array
    {
        /** @var array<string, list<string>> */
        return $this->response->getHeaders();
    }

    #[Override] public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    #[Override] public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    #[Override] public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    #[Override] public function getStatusLine(): string
    {
        $protocol = $this->response->getProtocolVersion();
        $code = $this->response->getStatusCode();
        $reasonPhrase = $this->response->getReasonPhrase();

        return "HTTP/$protocol $code $reasonPhrase";
    }

    #[Override] public function getTime(): int
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function hasHeader(string $name): bool
    {
        return $this->response->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function header(string $name): array
    {
        /** @var list<string> */
        return $this->response->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function headers(): array
    {
        /** @var array<string, list<string>> */
        return $this->response->getHeaders();
    }

    #[Override] public function path(string $path): mixed
    {
        throw new LogicException('Not implemented yet');
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
        return $this->response->getStatusCode();
    }

    #[Override] public function statusLine(): string
    {
        return $this->getStatusLine();
    }

    #[Override] public function then(): ValidatableResponseOptions
    {
        return $this->validatableResponseOptions;
    }

    #[Override] public function thenReturn(): static
    {
        return $this;
    }

    #[Override] public function time(): int
    {
        throw new LogicException('Not implemented yet');
    }

    /**
     * @inheritDoc
     */
    #[Override] public function withAddedHeader(string $name, $value): never
    {
        throw new NotImplemented(
            'The withAddedHeader() method is not implemented, since it does not '
            . 'make sense to manipulate the response in the context of REST Certain',
        );
    }

    #[Override] public function withBody(StreamInterface $body): never
    {
        throw new NotImplemented(
            'The withBody() method is not implemented, since it does not '
            . 'make sense to manipulate the response in the context of REST Certain',
        );
    }

    /**
     * @inheritDoc
     */
    #[Override] public function withHeader(string $name, $value): never
    {
        throw new NotImplemented(
            'The withHeader() method is not implemented, since it does not '
            . 'make sense to manipulate the response in the context of REST Certain',
        );
    }

    #[Override] public function withProtocolVersion(string $version): never
    {
        throw new NotImplemented(
            'The withProtocolVersion() method is not implemented, since it does not '
            . 'make sense to manipulate the response in the context of REST Certain',
        );
    }

    #[Override] public function withStatus(int $code, string $reasonPhrase = ''): never
    {
        throw new NotImplemented(
            'The withStatus() method is not implemented, since it does not '
            . 'make sense to manipulate the response in the context of REST Certain',
        );
    }

    #[Override] public function withoutHeader(string $name): never
    {
        throw new NotImplemented(
            'The withoutHeader() method is not implemented, since it does not '
            . 'make sense to manipulate the response in the context of REST Certain',
        );
    }
}
