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

namespace RestCertain\Constraint\Json;

use Closure;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Uri as JsonSchemaUri;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\UriInterface;
use RestCertain\Exception\JsonSchemaFailure;
use RestCertain\Exception\MissingConfiguration;
use RestCertain\Json\Json;
use RestCertain\Json\Schema\Config;
use RestCertain\RestCertain;
use Stringable;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

use function assert;
use function implode;

final class MatchesJsonSchema extends Constraint
{
    /**
     * @var array<string, string[]>
     */
    private array $errors = [];

    private JsonSchemaUri | string | null $schema = null;

    private ?Config $config;

    /**
     * @param Closure(): (JsonSchemaUri | string) $schemaLoader A closure that returns the schema contents
     *     at evaluation time, deferring any file or URI loading until then.
     */
    private function __construct(private readonly Closure $schemaLoader)
    {
        $this->config = RestCertain::$config?->jsonSchemaConfig;
    }

    #[Override] public function toString(): string
    {
        return 'matches JSON schema';
    }

    #[Override] protected function additionalFailureDescription(mixed $other): string
    {
        $errors = "\nFound the following JSON Schema validation errors:\n\n";
        foreach ($this->errors as $property => $messages) {
            $errors .= "  $property:\n    " . implode("\n  \t", $messages) . "\n\n";
        }

        return $errors;
    }

    #[Override] protected function matches(mixed $other): bool
    {
        // Reset the errors in case there are any remaining from a previous match attempt.
        $this->errors = [];

        if ($this->schema === null) {
            $this->schema = ($this->schemaLoader)();
        }

        $result = $this->config?->validator->validate(Helper::toJSON($other), $this->schema);

        if ($result === null) {
            throw new MissingConfiguration('No JSON Schema validator found. Please configure a JSON Schema validator.');
        }

        if (!$result->isValid()) {
            $errorResult = $result->error();
            assert($errorResult !== null);

            /** @var array<string, string[]> $errors */
            $errors = (new ErrorFormatter())->format($errorResult);
            $this->errors = $errors;

            return false;
        }

        return true;
    }

    /**
     * Creates a JSON Schema matcher for the JSON Schema represented by the given PHP array or object.
     *
     * @param array<string, mixed> | object $data The JSON Schema represented as a PHP array or object.
     */
    public static function fromData(array | object $data): self
    {
        return self::fromString(Json::encode($data));
    }

    /**
     * Creates a JSON Schema matcher for the JSON Schema at the given file path.
     *
     * @param Stringable | string $path The path to the JSON Schema file.
     */
    public static function fromFile(Stringable | string $path): self
    {
        $schemaLoader = function () use ($path): string {
            try {
                return (new Filesystem())->readFile((string) $path);
            } catch (IOException $exception) {
                throw new JsonSchemaFailure(message: $exception->getMessage(), previous: $exception);
            }
        };

        return new self($schemaLoader);
    }

    /**
     * Creates a JSON Schema matcher for the given JSON Schema.
     *
     * @param Stringable | string $schema The JSON Schema as a string.
     */
    public static function fromString(Stringable | string $schema): self
    {
        return new self(fn (): string => (string) $schema);
    }

    /**
     * Creates a JSON Schema matcher for the JSON Schema at the given URI.
     *
     * @param Stringable | UriInterface | string $uri The URI of the JSON Schema file.
     */
    public static function fromUri(Stringable | UriInterface | string $uri): self
    {
        $jsonSchemaUri = JsonSchemaUri::create((string) $uri);

        if (!$jsonSchemaUri instanceof JsonSchemaUri) {
            throw new JsonSchemaFailure("Invalid JSON Schema URI: $uri");
        }

        return new self(fn (): JsonSchemaUri => $jsonSchemaUri);
    }
}
