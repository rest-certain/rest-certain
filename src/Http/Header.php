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
 * Static constants for HTTP headers (a.k.a. fields).
 *
 * This is a subset of the fields defined in the
 * {@link https://www.iana.org/assignments/http-fields/http-fields.xhtml IANA HTTP Field Name Registry}.
 */
final class Header
{
    public const string ACCEPT = 'accept';
    public const string ACCEPT_ENCODING = 'accept-encoding';
    public const string ACCEPT_LANGUAGE = 'accept-language';
    public const string ACCEPT_PATCH = 'accept-patch';
    public const string ACCEPT_POST = 'accept-post';
    public const string ACCEPT_RANGES = 'accept-ranges';
    public const string ACCEPT_SIGNATURE = 'accept-signature';
    public const string AGE = 'age';
    public const string ALLOW = 'allow';
    public const string AUTHENTICATION_INFO = 'authentication-info';
    public const string AUTHORIZATION = 'authorization';
    public const string CACHE_CONTROL = 'cache-control';
    public const string CACHE_STATUS = 'cache-status';
    public const string CONTENT_DIGEST = 'content-digest';
    public const string CONTENT_DISPOSITION = 'content-disposition';
    public const string CONTENT_ENCODING = 'content-encoding';
    public const string CONTENT_LANGUAGE = 'content-language';
    public const string CONTENT_LENGTH = 'content-length';
    public const string CONTENT_LOCATION = 'content-location';
    public const string CONTENT_RANGE = 'content-range';
    public const string CONTENT_TYPE = 'content-type';
    public const string COOKIE = 'cookie';
    public const string DATE = 'date';
    public const string DEPRECATION = 'deprecation';
    public const string ETAG = 'etag';
    public const string EXPECT = 'expect';
    public const string EXPIRES = 'expires';
    public const string FORWARDED = 'forwarded';
    public const string FROM = 'from';
    public const string HOST = 'host';
    public const string IF_MATCH = 'if-match';
    public const string IF_MODIFIED_SINCE = 'if-modified-since';
    public const string IF_NONE_MATCH = 'if-none-match';
    public const string IF_RANGE = 'if-range';
    public const string IF_UNMODIFIED_SINCE = 'if-unmodified-since';
    public const string LAST_MODIFIED = 'last-modified';
    public const string LINK = 'link';
    public const string LINK_TEMPLATE = 'link-template';
    public const string LOCATION = 'location';
    public const string MAX_FORWARDS = 'max-forwards';
    public const string ORIGIN = 'origin';
    public const string PREFER = 'prefer';
    public const string PREFERENCE_APPLIED = 'preference-applied';
    public const string PROXY_AUTHORIZATION = 'proxy-authorization';
    public const string RANGE = 'range';
    public const string REFERER = 'referer';
    public const string REPR_DIGEST = 'repr-digest';
    public const string RETRY_AFTER = 'retry-after';
    public const string SERVER = 'server';
    public const string SET_COOKIE = 'set-cookie';
    public const string SIGNATURE = 'signature';
    public const string SIGNATURE_INPUT = 'signature-input';
    public const string SUNSET = 'sunset';
    public const string TRANSFER_ENCODING = 'transfer-encoding';
    public const string USER_AGENT = 'user-agent';
    public const string VARY = 'vary';
    public const string WANT_CONTENT_DIGEST = 'want-content-digest';
    public const string WANT_REPR_DIGEST = 'want-repr-digest';
    public const string WWW_AUTHENTICATE = 'www-authenticate';

    /**
     * Headers defined as singleton fields.
     *
     * @link https://www.rfc-editor.org/rfc/rfc9110.html#section-5.5-6 RFC 9110, 5.5. Field Values
     * @link https://www.rfc-editor.org/rfc/rfc9110.html#section-5.6.1 RFC 9110, 5.6.1. Lists (#rule ABNF Extension)
     * @link https://www.rfc-editor.org/rfc/rfc9110.html#appendix-A RFC 9110, Appendix A
     * @link https://www.rfc-editor.org/rfc/rfc9111.html#appendix-A RFC 9111, Appendix A
     * @link https://www.rfc-editor.org/rfc/rfc9745.html#section-2 RFC 9745, 2. The Deprecation HTTP Response Header Field
     * @link https://www.rfc-editor.org/rfc/rfc6454.html#section-7 RFC 6454, 7. The HTTP Origin Header Field
     * @link https://www.rfc-editor.org/rfc/rfc8594.html#section-3 RFC 8594, 3. The Sunset HTTP Response Header Field
     */
    public const array SINGLETON_HEADERS = [
        self::ACCEPT_RANGES,
        self::AGE,
        self::AUTHORIZATION,
        self::CONTENT_DISPOSITION,
        self::CONTENT_LENGTH,
        self::CONTENT_LOCATION,
        self::CONTENT_RANGE,
        self::CONTENT_TYPE,
        self::DATE,
        self::DEPRECATION,
        self::ETAG,
        self::EXPIRES,
        self::FROM,
        self::HOST,
        self::IF_MODIFIED_SINCE,
        self::IF_RANGE,
        self::IF_UNMODIFIED_SINCE,
        self::LAST_MODIFIED,
        self::LOCATION,
        self::MAX_FORWARDS,
        self::ORIGIN,
        self::PROXY_AUTHORIZATION,
        self::RANGE,
        self::REFERER,
        self::RETRY_AFTER,
        self::SERVER,
        self::SUNSET,
        self::USER_AGENT,
    ];

    /**
     * Disable public instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
