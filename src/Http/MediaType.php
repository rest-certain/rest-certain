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
 * Static constants for media types.
 *
 * This is a subset of the media types defined in the
 * {@link https://www.iana.org/assignments/media-types/media-types.xhtml IANA Media Types Registry}.
 */
final class MediaType
{
    public const string APPLICATION_ATOMCAT_XML = 'application/atomcat+xml';
    public const string APPLICATION_ATOMDELETED_XML = 'application/atomdeleted+xml';
    public const string APPLICATION_ATOMSVC_XML = 'application/atomsvc+xml';
    public const string APPLICATION_ATOM_XML = 'application/atom+xml';
    public const string APPLICATION_EXAMPLE = 'application/example';
    public const string APPLICATION_GZIP = 'application/gzip';
    public const string APPLICATION_HTTP = 'application/http';
    public const string APPLICATION_JSON = 'application/json';
    public const string APPLICATION_JSONPATH = 'application/jsonpath';
    public const string APPLICATION_JSON_PATCH = 'application/json-patch+json';
    public const string APPLICATION_JSON_SEQ = 'application/json-seq';
    public const string APPLICATION_JWT = 'application/jwt';
    public const string APPLICATION_LD_JSON = 'application/ld+json';
    public const string APPLICATION_OCTET_STREAM = 'application/octet-stream';
    public const string APPLICATION_PROBLEM_JSON = 'application/problem+json';
    public const string APPLICATION_PROBLEM_XML = 'application/problem+xml';
    public const string APPLICATION_SOAP_XML = 'application/soap+xml';
    public const string APPLICATION_WSDL_XML = 'application/wsdl+xml';
    public const string APPLICATION_XHTML_XML = 'application/xhtml+xml';
    public const string APPLICATION_XML = 'application/xml';
    public const string APPLICATION_XML_DTD = 'application/xml-dtd';
    public const string APPLICATION_XML_EXTERNAL_PARSED_ENTITY = 'application/xml-external-parsed-entity';
    public const string APPLICATION_XML_PATCH_XML = 'application/xml-patch+xml';
    public const string APPLICATION_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    public const string APPLICATION_YAML = 'application/yaml';
    public const string APPLICATION_ZIP = 'application/zip';
    public const string APPLICATION_ZLIB = 'application/zlib';
    public const string AUDIO_EXAMPLE = 'audio/example';
    public const string AUDIO_FLAC = 'audio/flac';
    public const string AUDIO_MP4 = 'audio/mp4';
    public const string AUDIO_MPEG = 'audio/mpeg';
    public const string FONT_OTF = 'font/otf';
    public const string FONT_TTF = 'font/ttf';
    public const string FONT_WOFF = 'font/woff';
    public const string FONT_WOFF2 = 'font/woff2';
    public const string IMAGE_EXAMPLE = 'image/example';
    public const string IMAGE_GIF = 'image/gif';
    public const string IMAGE_JPEG = 'image/jpeg';
    public const string IMAGE_PNG = 'image/png';
    public const string IMAGE_SVG = 'image/svg+xml';
    public const string IMAGE_TIFF = 'image/tiff';
    public const string IMAGE_WEBP = 'image/webp';
    public const string MESSAGE_EXAMPLE = 'message/example';
    public const string MESSAGE_HTTP = 'message/http';
    public const string MODEL_EXAMPLE = 'model/example';
    public const string MULTIPART_ALTERNATIVE = 'multipart/alternative';
    public const string MULTIPART_BYTERANGES = 'multipart/byteranges';
    public const string MULTIPART_EXAMPLE = 'multipart/example';
    public const string MULTIPART_FORM_DATA = 'multipart/form-data';
    public const string MULTIPART_MIXED = 'multipart/mixed';
    public const string TEXT_CALENDAR = 'text/calendar';
    public const string TEXT_CSS = 'text/css';
    public const string TEXT_CSV = 'text/csv';
    public const string TEXT_EXAMPLE = 'text/example';
    public const string TEXT_HTML = 'text/html';
    public const string TEXT_JAVASCRIPT = 'text/javascript';
    public const string TEXT_MARKDOWN = 'text/markdown';
    public const string TEXT_PLAIN = 'text/plain';
    public const string TEXT_VCARD = 'text/vcard';
    public const string TEXT_VTT = 'text/vtt';
    public const string TEXT_XML = 'text/xml';
    public const string TEXT_XML_EXTERNAL_PARSED_ENTITY = 'text/xml-external-parsed-entity';
    public const string VIDEO_EXAMPLE = 'video/example';
    public const string VIDEO_MP4 = 'video/mp4';
    public const string VIDEO_MPEG = 'video/mpeg';
    public const string VIDEO_QUICKTIME = 'video/quicktime';

    /**
     * Disable public instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
