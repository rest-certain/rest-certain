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

namespace RestCertain\Internal\Type;

use JsonSerializable;
use Override;
use stdClass;

use function json_encode;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * A value parsed from a JSON body.
 *
 * @internal This class is not intended for direct use outside of Rest Certain.
 */
final readonly class JsonValue implements JsonSerializable, ParsedType
{
    /**
     * @param stdClass | bool | float | int | mixed[] | string | null $value
     */
    public function __construct(private stdClass | array | bool | float | int | string | null $value)
    {
    }

    #[Override] public function __toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return stdClass | bool | float | int | mixed[] | string | null
     */
    #[Override] public function getValue(): stdClass | array | bool | float | int | string | null
    {
        return $this->value;
    }

    /**
     * @return stdClass | bool | float | int | mixed[] | string | null
     */
    #[Override] public function jsonSerialize(): stdClass | array | bool | float | int | string | null
    {
        return $this->value;
    }
}
