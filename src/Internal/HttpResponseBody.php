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

use Loilo\JsonPath\JsonPath;
use Loilo\JsonPath\SyntaxError;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RestCertain\Exception\NotImplemented;
use RestCertain\Exception\PathResolutionFailure;
use RestCertain\Internal\Type\ByteArray;
use RestCertain\Internal\Type\JsonValue;
use RestCertain\Internal\Type\ParsedType;
use RestCertain\Response\ResponseBody;
use RoNoLo\JsonQuery\JsonQuery;
use RoNoLo\JsonQuery\ValueNotFound;
use stdClass;

use function is_array;
use function json_decode;
use function json_last_error;
use function str_starts_with;

use const JSON_BIGINT_AS_STRING;
use const JSON_ERROR_NONE;
use const JSON_INVALID_UTF8_SUBSTITUTE;
use const SEEK_SET;

/**
 * @internal This class is not intended for direct use outside of Rest Certain.
 */
final class HttpResponseBody implements ResponseBody, StreamInterface
{
    private readonly StreamInterface $psrStream;
    private ParsedType $parsedBody;

    public function __construct(ResponseInterface $response)
    {
        $this->psrStream = $response->getBody();
    }

    public function __toString(): string
    {
        $this->psrStream->rewind();

        return $this->psrStream->getContents();
    }

    #[Override] public function asPrettyString(): string
    {
        throw new NotImplemented(__METHOD__ . ' is not yet implemented');
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
        $parsedBody = $this->getParsedBody();

        if (!$parsedBody instanceof JsonValue) {
            throw new PathResolutionFailure(
                "The response body is not a valid JSON value.\nReceived:\n" . $this->asString(),
            );
        }

        if (str_starts_with($path, '$')) {
            try {
                $jsonPath = new JsonPath($path);

                return $jsonPath->find($parsedBody->getValue());
            } catch (SyntaxError $exception) {
                throw new PathResolutionFailure(
                    message: 'Unable to parse JSONPath query: ' . $exception->getMessage(),
                    previous: $exception,
                );
            }
        }

        if (!is_array($parsedBody->getValue()) && !$parsedBody->getValue() instanceof stdClass) {
            throw new PathResolutionFailure('Unable to use a path on a JSON value that is not an object or array');
        }

        $value = JsonQuery::fromValidData($parsedBody->getValue())->query($path);

        if ($value instanceof ValueNotFound) {
            throw new PathResolutionFailure('Unable to find the path "' . $path . '" in the JSON value');
        }

        return $value;
    }

    #[Override] public function prettyPrint(): string
    {
        throw new NotImplemented(__METHOD__ . ' is not yet implemented');
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

    private function getParsedBody(): ParsedType
    {
        if (isset($this->parsedBody)) {
            return $this->parsedBody;
        }

        /** @var stdClass | bool | float | int | mixed[] | string | null $parsedBody */
        $parsedBody = json_decode(json: $this->asString(), flags: JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($parsedBody === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->parsedBody = new ByteArray($this->asString());
        } else {
            $this->parsedBody = new JsonValue($parsedBody);
        }

        return $this->parsedBody;
    }
}
