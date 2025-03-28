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

namespace RestCertain\Http;

/**
 * Static constants for HTTP methods.
 *
 * This is a subset of the methods defined in the
 * {@link https://www.iana.org/assignments/http-methods/http-methods.xhtml IANA HTTP Method Registry}.
 */
final class Method
{
    public const string DELETE = 'DELETE';
    public const string GET = 'GET';
    public const string HEAD = 'HEAD';
    public const string OPTIONS = 'OPTIONS';
    public const string PATCH = 'PATCH';
    public const string POST = 'POST';
    public const string PUT = 'PUT';

    /**
     * Disable public instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
