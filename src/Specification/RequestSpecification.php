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

namespace RestCertain\Specification;

use JsonSerializable;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use SplFileInfo;
use Stringable;

interface RequestSpecification extends RequestSender
{
    /**
     * Specifies the Accept header for the request.
     *
     * This is shorthand for the {@see self::header()} method:
     *
     * ```
     * $specification->header('Accept', 'application/json');
     * ```
     *
     * @return $this
     */
    public function accept(Stringable | string $contentType): static;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function and(): static;

    /**
     * Returns an {@see AuthenticationSpecification} instance that may be used to specify authentication credentials
     * for the request.
     */
    public function auth(): AuthenticationSpecification;

    /**
     * Sets the base path for the request.
     *
     * @return $this
     */
    public function basePath(Stringable | string $basePath): static;

    /**
     * Sets the base URI for the request.
     *
     * @return $this
     */
    public function baseUri(Stringable | UriInterface | string $baseUri): static;

    /**
     * Sets the body value for the request.
     *
     * @return $this
     */
    public function body(JsonSerializable | SplFileInfo | StreamInterface | Stringable | string $body): static;

    /**
     * Sets the content type for the request.
     *
     * @return $this
     */
    public function contentType(Stringable | string $contentType): static;

    /**
     * Sets a cookie that will be sent with the request.
     *
     * @param string $name The name of the cookie.
     * @param Stringable | string | null $value The value of the cookie.
     *
     * @return $this
     */
    public function cookie(string $name, Stringable | string | null $value = null): static;

    /**
     * Sets the cookies that will be sent with the request.
     *
     * @param array<string, Stringable | string | null> $cookies
     *
     * @return $this
     */
    public function cookies(array $cookies): static;

    /**
     * Returns the response specification to set expectations on the response.
     */
    public function expect(): ResponseSpecification;

    /**
     * Sets a form parameter that will be sent with the request.
     *
     * Note that parameters may have multiple values. Each value will be sent in the request with the same name. For
     * example:
     *
     *     $specification->formParam('cheese', 'cheddar', 'havarti', 'swiss');
     *     $specification->formParam('crackers', 'yes');
     *
     * Will send the following data:
     *
     *     cheese=cheddar&cheese=havarti&cheese=swiss&crackers=yes
     *
     * Note that this method is the same as {@see self::param()} for all HTTP methods except for `PUT` or `PATCH`, where
     * {@see self::param()} will treat the params as query string parameters, while this method always treats them as
     * form parameters.
     *
     * @return $this
     */
    public function formParam(
        string $name,
        Stringable | string $value,
        Stringable | string ...$additionalValues,
    ): static;

    /**
     * Sets form parameters that will be sent with the request.
     *
     * Each parameter may be a single value or an array of values. If the value is an array, each value will be sent in
     * the request with the same name. For example:
     *
     *     $specification->formParams([
     *         'cheese' => ['edam', 'brie', 'gruyère'],
     *         'crackers' => 'yes',
     *     ]);
     *
     * Will send the following data:
     *
     *     cheese=edam&cheese=brie&cheese=gruy%C3%A8re&crackers=yes
     *
     * Note that this method is the same as {@see self::params()} for all HTTP methods except for `PUT` or `PATCH`,
     * where {@see self::params()} will treat the params as query string parameters, while this method always treats
     * them as form parameters.
     *
     * @param array<string, Stringable | string | list<Stringable | string>> $parameters
     *
     * @return $this
     */
    public function formParams(array $parameters): static;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function given(): static;

    /**
     * Sets a header that will be sent with the request.
     *
     * If there are multiple values, the header is treated as a multi-value header. That is, there will be multiple
     * headers with the same name and different values.
     *
     * @return $this
     */
    public function header(
        string $name,
        Stringable | string $value,
        Stringable | string ...$additionalValues,
    ): static;

    /**
     * Sets headers that will be sent with the request.
     *
     * If a name has a single value, the header is treated as a single-value header. If the value is an array, then the
     * header is treated as a multi-value header. That is, there will be multiple headers with the same name and
     * different values.
     *
     * @param array<string, Stringable | string | list<Stringable | string>> $headers
     *
     * @return $this
     */
    public function headers(array $headers): static;

    /**
     * Sets a parameter that will be sent with the request.
     *
     * Note that parameters may have multiple values. Each value will be sent in the request with the same name. For
     * example:
     *
     *     $specification->param('cheese', 'ricotta', 'blue stilton', 'comté');
     *     $specification->param('crackers', 'no');
     *
     * Will send the following data:
     *
     *     cheese=ricotta&cheese=blue+stilton&cheese=comt%C3%A9&crackers=no
     *
     * Note that parameters are treated as query string parameters for all HTTP requests except for `POST`, where they
     * are sent in the body. If you want to send parameters in the body on `PUT` or `PATCH` requests, use
     * {@see self::formParams()} instead. If you want to send parameters in the query string on `POST` requests, use
     * {@see self::queryParams()} instead.
     *
     * @return $this
     */
    public function param(
        string $name,
        Stringable | string $value,
        Stringable | string ...$additionalValues,
    ): static;

    /**
     * Sets parameters that will be sent with the request.
     *
     * Each parameter may be a single value or an array of values. If the value is an array, each value will be sent in
     * the request with the same name. For example:
     *
     *     $specification->formParams([
     *         'cheese' => ['emmental', 'bergkäse', 'langres'],
     *         'crackers' => 'yes',
     *     ]);
     *
     * Will send the following data:
     *
     *     cheese=emmental&cheese=bergk%C3%A4se&cheese=langres&crackers=yes
     *
     * Note that parameters are treated as query string parameters for all HTTP requests except for `POST`, where they
     * are sent in the body. If you want to send parameters in the body on `PUT` or `PATCH` requests, use
     * {@see self::formParams()} instead. If you want to send parameters in the query string on `POST` requests, use
     * {@see self::queryParams()} instead.
     *
     * @param array<string, Stringable | string | list<Stringable | string>> $parameters
     *
     * @return $this
     */
    public function params(array $parameters): static;

    /**
     * Sets a path parameter to improve readability of the request path.
     *
     * For example, instead of:
     *
     * ```
     * when()->
     *     get('/users/' . $userId . '/posts/' . $postId)->
     * then()->
     *     statusCode(200);
     * ```
     *
     * You may write:
     *
     * ```
     * given()->
     *     pathParam('userId', $userId)->
     *     pathParam('postId', $postId)->
     * when()->
     *     get('/users/{userId}/posts/{postId}')->
     * then()->
     *     statusCode(200);
     * ```
     *
     * @return $this
     */
    public function pathParam(string $name, Stringable | int | string $value): static;

    /**
     * Sets multiple path parameters to improve readability of the request path.
     *
     * @see self::pathParam() for more information about usage.
     *
     * @param array<string, Stringable | int | string> $parameters
     *
     * @return $this
     */
    public function pathParams(array $parameters): static;

    /**
     * Sets the port to use as part of the request URI.
     *
     * @return $this
     */
    public function port(int $port): static;

    /**
     * Sets a query string parameter that will be sent with the request.
     *
     * Note that parameters may have multiple values. Each value will be sent in the request with the same name. For
     * example:
     *
     *     $specification->param('cheese', 'paneer', 'nguri', 'ayibe');
     *     $specification->param('crackers', 'no');
     *
     * Will send the following data:
     *
     *     cheese=paneer&cheese=nguri&cheese=ayibe&crackers=no
     *
     * Note that this method is the same as {@see self::param()} for all HTTP methods except for `POST`, where
     * {@see self::param()} will treat the params as form parameters, while this method always treats them as query
     * string parameters.
     *
     * @return $this
     */
    public function queryParam(
        string $name,
        Stringable | string $value,
        Stringable | string ...$additionalValues,
    ): static;

    /**
     * Sets query string parameters that will be sent with the request.
     *
     * Each parameter may be a single value or an array of values. If the value is an array, each value will be sent in
     * the request with the same name. For example:
     *
     *     $specification->formParams([
     *         'cheese' => ['Sakura cheese', 'feta', 'höfðingi'],
     *         'crackers' => 'yes',
     *     ]);
     *
     * Will send the following data:
     *
     *     cheese=Sakura+cheese&cheese=feta&cheese=h%C3%B6f%C3%B0ingi&crackers=yes
     *
     * Note that this method is the same as {@see self::params()} for all HTTP methods except for `POST`, where
     * {@see self::params()} will treat the params as form parameters, while this method always treats them as query
     * string parameters.
     *
     * @param array<string, Stringable | string | list<Stringable | string>> $parameters
     *
     * @return $this
     */
    public function queryParams(array $parameters): static;

    /**
     * Returns a {@see RedirectSpecification} instance that may be used to configure redirection handling for the
     * request.
     */
    public function redirects(): RedirectSpecification;

    /**
     * Returns the response specification to set expectations on the response.
     */
    public function response(): ResponseSpecification;

    /**
     * Sets the response specification to use with this request specification.
     *
     * @return $this
     */
    public function setResponseSpecification(ResponseSpecification $responseSpecification): static;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function that(): static;

    /**
     * Returns the response specification to set expectations on the response.
     */
    public function then(): ResponseSpecification;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function when(): static;

    /**
     * Syntactic sugar, this returns the same instance.
     *
     * @return $this
     */
    public function with(): static;
}
