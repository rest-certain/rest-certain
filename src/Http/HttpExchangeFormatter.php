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

use Neoncitylights\MediaType\MediaTypeParser;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RestCertain\Exception\UnableToDecodeJson;
use RestCertain\Json\Json;

use function implode;
use function sprintf;
use function str_contains;
use function strtolower;

final readonly class HttpExchangeFormatter
{
    private MediaTypeParser $mediaTypeParser;

    public function __construct()
    {
        $this->mediaTypeParser = new MediaTypeParser();
    }

    public function print(RequestInterface $request, ResponseInterface $response): void
    {
        echo $this->format($request, $response);
    }

    public function format(RequestInterface $request, ResponseInterface $response): string
    {
        $messages = [];

        $messages[] = $this->formatRequestLine($request);
        $messages[] = $this->formatHeaders($request);
        $messages[] = $this->formatBody($request);
        $messages[] = "\n\n";
        $messages[] = $this->formatStatusLine($response);
        $messages[] = $this->formatHeaders($response);
        $messages[] = $this->formatBody($response);
        $messages[] = "\n\n";

        return implode('', $messages);
    }

    private function formatBody(MessageInterface $message): string
    {
        $body = (string) $message->getBody();

        if ($body === '') {
            return '';
        }

        try {
            $mediaType = $this->mediaTypeParser->parseOrNull($message->getHeaderLine('content-type'));

            $body = match (true) {
                $mediaType?->isJson() => Json::encode(Json::decode($body), true),
                $mediaType?->isImage() => '[image data]',
                $mediaType?->isArchive(), $mediaType?->isZipBased() => '[archive data]',
                $mediaType?->isAudioOrVideo() => '[audio/video data]',
                $mediaType?->isFont() => '[font data]',
                $mediaType?->getEssence() === 'application/pdf' => '[PDF data]',
                str_contains($body, "\0") => '[binary data]',
                default => $body,
            };
        } catch (UnableToDecodeJson) {
            // We couldn't decode the body as JSON to re-encode it and prettify it,
            // so we'll just use the body as-is.
        }

        return "\r\n\r\n$body";
    }

    private function formatHeaders(MessageInterface $message): string
    {
        $headers = [];
        foreach ($message->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headers[] = strtolower($name) . ': ' . $value;
            }
        }

        if ($headers !== []) {
            return "\r\n" . implode("\r\n", $headers);
        }

        return '';
    }

    private function formatRequestLine(RequestInterface $request): string
    {
        return sprintf(
            '%s %s HTTP/%s',
            $request->getMethod(),
            $request->getRequestTarget(),
            $request->getProtocolVersion(),
        );
    }

    private function formatStatusLine(ResponseInterface $response): string
    {
        $reasonPhrase = $response->getReasonPhrase();

        return sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $reasonPhrase !== '' ? ' ' . $reasonPhrase : '',
        );
    }
}
