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

use LogicException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Response\ResponseBody;

use const SEEK_SET;

/**
 * @internal
 */
final readonly class ResponseBodyImpl implements ResponseBody, StreamInterface
{
    private StreamInterface $stream;

    public function __construct(private ResponseInterface $response)
    {
        $this->stream = $this->response->getBody();
    }

    public function __toString(): string
    {
        $this->stream->rewind();

        return $this->stream->getContents();
    }

    #[Override] public function asPrettyString(): string
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function asString(): string
    {
        return (string) $this;
    }

    #[Override] public function close(): void
    {
        $this->stream->close();
    }

    /**
     * @inheritDoc
     */
    #[Override] public function detach()
    {
        return $this->stream->detach();
    }

    #[Override] public function eof(): bool
    {
        return $this->stream->eof();
    }

    #[Override] public function getContents(): string
    {
        return $this->stream->getContents();
    }

    /**
     * @inheritDoc
     */
    #[Override] public function getMetadata(?string $key = null)
    {
        return $this->stream->getMetadata($key);
    }

    #[Override] public function getSize(): ?int
    {
        return $this->stream->getSize();
    }

    #[Override] public function isReadable(): bool
    {
        return $this->stream->isReadable();
    }

    #[Override] public function isSeekable(): bool
    {
        return $this->stream->isSeekable();
    }

    #[Override] public function isWritable(): bool
    {
        return $this->stream->isWritable();
    }

    #[Override] public function path(string $path): mixed
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function prettyPrint(): string
    {
        throw new LogicException('Not implemented yet');
    }

    #[Override] public function print(): string
    {
        $body = $this->asString();
        echo $body;

        return $body;
    }

    #[Override] public function read(int $length): string
    {
        return $this->stream->read($length);
    }

    #[Override] public function rewind(): void
    {
        $this->stream->rewind();
    }

    #[Override] public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->stream->seek($offset, $whence);
    }

    #[Override] public function tell(): int
    {
        return $this->stream->tell();
    }

    #[Override] public function write(string $string): int
    {
        return $this->stream->write($string);
    }
}
