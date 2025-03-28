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
    public const string ORIGIN = 'origin';
    public const string PREFER = 'prefer';
    public const string PREFERENCE_APPLIED = 'preference-applied';
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
     * Disable public instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
