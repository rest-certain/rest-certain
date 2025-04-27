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
    private ResponseInterface $psrResponse;
    private StreamInterface $psrStream;

    public function __construct(ResponseInterface $response)
    {
        $this->psrResponse = $response;
        $this->psrStream = $this->psrResponse->getBody();
    }

    public function __toString(): string
    {
        $this->psrStream->rewind();

        return $this->psrStream->getContents();
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
        $this->psrStream->close();
    }

    /**
     * @inheritDoc
     */
    #[Override] public function detach()
    {
        return $this->psrStream->detach();
    }

    #[Override] public function eof(): bool
    {
        return $this->psrStream->eof();
    }

    #[Override] public function getContents(): string
    {
        return $this->psrStream->getContents();
    }

    /**
     * @inheritDoc
     */
    #[Override] public function getMetadata(?string $key = null)
    {
        return $this->psrStream->getMetadata($key);
    }

    #[Override] public function getSize(): ?int
    {
        return $this->psrStream->getSize();
    }

    #[Override] public function isReadable(): bool
    {
        return $this->psrStream->isReadable();
    }

    #[Override] public function isSeekable(): bool
    {
        return $this->psrStream->isSeekable();
    }

    #[Override] public function isWritable(): bool
    {
        return $this->psrStream->isWritable();
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
        return $this->psrStream->read($length);
    }

    #[Override] public function rewind(): void
    {
        $this->psrStream->rewind();
    }

    #[Override] public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->psrStream->seek($offset, $whence);
    }

    #[Override] public function tell(): int
    {
        return $this->psrStream->tell();
    }

    #[Override] public function write(string $string): int
    {
        return $this->psrStream->write($string);
    }
}
