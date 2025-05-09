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

use Closure;
use Opis\JsonSchema\CompliantValidator;
use Opis\JsonSchema\Resolvers\SchemaResolver;
use Opis\JsonSchema\Uri as JsonSchemaUri;
use Opis\JsonSchema\Validator;
use PHPUnit\Util\Exporter;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use RestCertain\Config as RestCertainConfig;
use RestCertain\Exception\JsonSchemaFailure;
use RestCertain\Http\Header;
use RestCertain\Http\Method;
use RestCertain\Json\Json;
use RestCertain\RestCertain;
use stdClass;

use function sprintf;

/**
 * Configuration for loading and parsing JSON Schemas to use as response body validators.
 */
final readonly class Config
{
    public const int DEFAULT_MAX_ERRORS = 10;
    public const bool DEFAULT_STOP_ON_FIRST_ERROR = false;

    /**
     * @internal This property is not intended for direct use outside of Rest Certain. It may change without notice.
     */
    public SchemaResolver $resolver;

    /**
     * @internal This property is not intended for direct use outside of Rest Certain. It may change without notice.
     */
    public Validator $validator;

    /**
     * @param array<string, Closure(UriInterface $uri): stdClass> $protocolResolvers Closures to use for resolving JSON
     *     Schemas from URIs; each Closure resolves for a different protocol, as noted by the key (i.e. `http`, `https`,
     *     etc.).
     * @param array<string, string> $prefixResolvers Directory paths to load JSON Schemas from; each key is a `$ref` ID
     *     prefix, and each value is a path to a directory; for example, if a JSON Schema has a `$ref` ID of
     *     `https://example.com/schemas/foo.json`, and the prefix is specified as `https://example.com/schemas/`, then
     *     the path `foo.json` will be loaded from the directory specified.
     */
    public function __construct(
        public RestCertainConfig $config,
        array $protocolResolvers = [],
        array $prefixResolvers = [],
        public int $maxErrors = self::DEFAULT_MAX_ERRORS,
        public bool $stopOnFirstError = self::DEFAULT_STOP_ON_FIRST_ERROR,
    ) {
        $this->resolver = new SchemaResolver();

        foreach ($protocolResolvers as $protocol => $resolver) {
            $this->resolver->registerProtocol($protocol, $this->wrapResolver($resolver));
        }

        foreach ($prefixResolvers as $prefix => $path) {
            $this->resolver->registerPrefix($prefix, $path);
        }

        if (!isset($protocolResolvers['http'])) {
            $this->resolver->registerProtocol('http', $this->defaultHttpResolver());
        }

        if (!isset($protocolResolvers['https'])) {
            $this->resolver->registerProtocol('https', $this->defaultHttpResolver());
        }

        $this->validator = (new CompliantValidator())
            ->setMaxErrors($this->maxErrors)
            ->setStopAtFirstError($this->stopOnFirstError)
            ->setResolver($this->resolver);
    }

    /**
     * @param Closure(UriInterface $uri): stdClass $resolver
     *
     * @return Closure(JsonSchemaUri): stdClass
     */
    private function wrapResolver(Closure $resolver): Closure
    {
        return fn (JsonSchemaUri $uri): stdClass => $resolver($this->config->uriFactory->createUri((string) $uri));
    }

    /**
     * @return Closure(JsonSchemaUri): stdClass
     */
    private function defaultHttpResolver(): Closure
    {
        return function (JsonSchemaUri $uri): stdClass {
            $psrUri = $this->config->uriFactory->createUri((string) $uri);

            $psrResponse = $this->sendSchemaRequest(
                $this->config->requestFactory
                    ->createRequest(Method::GET, $psrUri)
                    ->withHeader(Header::USER_AGENT, RestCertain::USER_AGENT),
            );

            return $this->decodeSchema($psrResponse->getBody()->getContents(), $psrUri);
        };
    }

    private function sendSchemaRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->config->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new JsonSchemaFailure(
                message: sprintf(
                    'Encountered an error while attempting to fetch the JSON Schema from %s: %s',
                    $request->getUri(),
                    $exception->getMessage(),
                ),
                previous: $exception,
            );
        }

        $this->checkSchemaStatusCode($request, $response);

        return $response;
    }

    private function checkSchemaStatusCode(RequestInterface $request, ResponseInterface $response): void
    {
        if ($response->getStatusCode() !== 200) {
            $contents = $response->getBody()->getContents();

            throw new JsonSchemaFailure(sprintf(
                'Received status code %d when attempting to fetch the JSON Schema from %s%s',
                $response->getStatusCode(),
                $request->getUri(),
                ($contents ? "\n\n$contents\n" : ''),
            ));
        }
    }

    private function decodeSchema(string $schema, UriInterface $uri): stdClass
    {
        $schema = Json::decode($schema);

        if (!$schema instanceof stdClass) {
            throw new JsonSchemaFailure(sprintf(
                "JSON Schema did not decode to an object.\n\nSchema URI: %s\nDecoded value: %s\n",
                $uri,
                Exporter::export($schema),
            ));
        }

        return $schema;
    }
}
