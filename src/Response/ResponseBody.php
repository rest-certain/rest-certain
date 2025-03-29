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

namespace RestCertain\Response;

use Psr\Http\Message\StreamInterface;

interface ResponseBody extends StreamInterface
{
    /**
     * Returns the body as a "pretty" formatted string.
     */
    public function asPrettyString(): string;

    /**
     * Returns the body as a string.
     */
    public function asString(): string;

    /**
     * Returns a value from the response body using JSONPath syntax.
     *
     * In the future, this may also support XPath syntax.
     */
    public function path(string $path): mixed;

    /**
     * Pretty-prints the response body if possible and returns it as string.
     *
     * This is mainly useful for debug purposes when writing tests. Pretty-printing is possible for content-types
     * JSON, XML and HTML.
     */
    public function prettyPrint(): string;

    /**
     * Prints the response body and returns it as string.
     *
     * This is mainly useful for debug purposes when writing tests.
     */
    public function print(): string;
}
