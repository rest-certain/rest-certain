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

interface ExtractableResponseBody
{
    /**
     * Returns the body as a "pretty" formatted string.
     */
    public function asPrettyString(): string;

    /**
     * Returns the body as a PSR-7 StreamInterface.
     */
    public function asPsrStream(): StreamInterface;

    /**
     * Returns the body as a string.
     */
    public function asString(): string;

    /**
     * Returns a value from the response body using JSONPath or JMESPath syntax.
     *
     * In the future, this may also support XPath syntax.
     *
     * @link https://www.rfc-editor.org/rfc/rfc9535.html JSONPath
     * @link https://jmespath.org JMESPath
     */
    public function path(string $path): mixed;
}
