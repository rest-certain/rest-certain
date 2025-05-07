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

namespace RestCertain\Json\Schema;

use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\UriInterface;
use Stringable;

// phpcs:disable Squiz.Functions.GlobalFunction.Found

/**
 * Creates a matcher that validates that a JSON document conforms to the JSON Schema provided.
 *
 * @param Stringable | string $schema The JSON Schema as a string.
 */
function matchesJsonSchema(Stringable | string $schema): Constraint
{
    return Matchers::matchesJsonSchema($schema);
}

/**
 * Creates a matcher that validates that a JSON document conforms to the JSON Schema provided.
 *
 * @param array<string, mixed> | object $data The JSON Schema represented as a PHP array or object.
 */
function matchesJsonSchemaFromData(array | object $data): Constraint
{
    return Matchers::matchesJsonSchemaFromData($data);
}

/**
 * Creates a matcher that validates that a JSON document conforms to the JSON Schema provided.
 *
 * @param Stringable | string $path The path to the JSON Schema file.
 */
function matchesJsonSchemaFromFile(Stringable | string $path): Constraint
{
    return Matchers::matchesJsonSchemaFromFile($path);
}

/**
 * Creates a matcher that validates that a JSON document conforms to the JSON Schema provided.
 *
 * @param Stringable | UriInterface | string $uri The URI of the JSON Schema file.
 */
function matchesJsonSchemaFromUri(Stringable | UriInterface | string $uri): Constraint
{
    return Matchers::matchesJsonSchemaFromUri($uri);
}
