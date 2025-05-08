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

namespace RestCertain\Json;

use RestCertain\Exception\UnableToDecodeJson;
use RestCertain\Exception\UnableToEncodeJson;
use stdClass;

use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;

use const JSON_BIGINT_AS_STRING;
use const JSON_ERROR_NONE;
use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @internal This class is not intended for direct use outside of Rest Certain.
 */
final class Json
{
    private const int DECODE = JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_SUBSTITUTE;
    private const int ENCODE = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    private const int PRETTY = self::ENCODE | JSON_PRETTY_PRINT;

    public static function encode(mixed $value, bool $pretty = false): string
    {
        $value = json_encode($value, $pretty ? self::PRETTY : self::ENCODE);

        if ($value === false) {
            throw new UnableToEncodeJson('Failed to encode value to JSON: ' . json_last_error_msg());
        }

        return $value;
    }

    /**
     * @return stdClass | array<mixed> | bool | float | int | string | null
     */
    public static function decode(string $value): stdClass | array | bool | float | int | string | null
    {
        /** @var stdClass | array<mixed> | bool | float | int | string | null $decoded */
        $decoded = json_decode(json: $value, flags: self::DECODE);

        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new UnableToDecodeJson('Failed to decode value as JSON: ' . json_last_error_msg());
        }

        return $decoded;
    }
}
