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

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Response\ExtractableResponse;
use RestCertain\Response\ExtractableResponseBody;
use RestCertain\Response\Response;

/**
 * @internal This class is not intended for direct use outside of Rest Certain.
 */
final readonly class ResponseExtractor implements ExtractableResponse
{
    private ExtractableResponseBody $body;

    public function __construct(private Response $response)
    {
        $this->body = new ResponseBodyExtractor($this->response->body());
    }

    #[Override] public function asPrettyString(): string
    {
        return $this->body->asPrettyString();
    }

    #[Override] public function asPsrStream(): StreamInterface
    {
        return $this->body->asPsrStream();
    }

    #[Override] public function asString(): string
    {
        return $this->body->asString();
    }

    #[Override] public function body(): ExtractableResponseBody
    {
        return $this->body;
    }

    #[Override] public function contentType(): ?string
    {
        return $this->response->contentType();
    }

    #[Override] public function cookie(string $name): ?string
    {
        return $this->response->cookie($name);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function cookies(): array
    {
        return $this->response->cookies();
    }

    /**
     * @inheritDoc
     */
    #[Override] public function header(string $name): array
    {
        return $this->response->header($name);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function headers(): array
    {
        return $this->response->headers();
    }

    #[Override] public function path(string $path): mixed
    {
        return $this->body->path($path);
    }

    #[Override] public function response(): ResponseInterface
    {
        return $this->response;
    }

    #[Override] public function statusCode(): int
    {
        return $this->response->statusCode();
    }

    #[Override] public function statusLine(): string
    {
        return $this->response->statusLine();
    }

    #[Override] public function time(): int
    {
        return $this->response->time();
    }
}
