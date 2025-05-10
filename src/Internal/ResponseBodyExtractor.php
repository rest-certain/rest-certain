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
use Psr\Http\Message\StreamInterface;
use RestCertain\Response\ExtractableResponseBody;
use RestCertain\Response\ResponseBody;

/**
 * @internal This class is not intended for direct use outside of Rest Certain.
 */
final readonly class ResponseBodyExtractor implements ExtractableResponseBody
{
    public function __construct(private ResponseBody & StreamInterface $body)
    {
    }

    #[Override] public function asPrettyString(): string
    {
        return $this->body->asPrettyString();
    }

    #[Override] public function asPsrStream(): StreamInterface
    {
        return $this->body;
    }

    #[Override] public function asString(): string
    {
        return $this->body->asString();
    }

    #[Override] public function path(string $path): mixed
    {
        return $this->body->path($path);
    }
}
