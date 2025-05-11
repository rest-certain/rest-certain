<?php

declare(strict_types=1);

namespace RestCertain\Test\Http;

use Laminas\Diactoros\Request\Serializer as RequestSerializer;
use Laminas\Diactoros\Response\Serializer as ResponseSerializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RestCertain\Http\HttpExchangeFormatter;

class HttpExchangeFormatterTest extends TestCase
{
    public function testPrintAndFormat(): void
    {
        $cr = "\r";

        $rawRequest = <<<HTTP_REQUEST
            POST /users HTTP/1.1$cr
            Host: example.com$cr
            Content-Type: application/x-www-form-urlencoded$cr
            Content-Length: 50$cr
            $cr
            name=FirstName%20LastName&email=bsmth%40example.com
            HTTP_REQUEST;

        $rawResponse = <<<HTTP_RESPONSE
            HTTP/1.1 201 Created$cr
            Content-Type: application/json$cr
            Location: https://example.com/users/123$cr
            $cr
            {
              "message": "New user created",
              "user": {
                "id": 123,
                "firstName": "Example",
                "lastName": "Person",
                "email": "bsmth@example.com"
              }
            }
            HTTP_RESPONSE;

        $expected = <<<EXPECTED
            POST /users HTTP/1.1$cr
            host: example.com$cr
            content-type: application/x-www-form-urlencoded$cr
            content-length: 50$cr
            $cr
            name=FirstName%20LastName&email=bsmth%40example.com

            HTTP/1.1 201 Created$cr
            content-type: application/json$cr
            location: https://example.com/users/123$cr
            $cr
            {
                "message": "New user created",
                "user": {
                    "id": 123,
                    "firstName": "Example",
                    "lastName": "Person",
                    "email": "bsmth@example.com"
                }
            }


            EXPECTED;

        $request = RequestSerializer::fromString($rawRequest);
        $response = ResponseSerializer::fromString($rawResponse);

        $formatter = new HttpExchangeFormatter();
        $formatted = $formatter->format($request, $response);

        $this->assertSame($expected, $formatted);

        $this->expectOutputString($expected);
        $formatter->print($request, $response);
    }

    #[DataProvider('mediaTypeProvider')]
    public function testFormatWithDifferentMediaTypes(string $mediaType, string $expectedResponseBody): void
    {
        $cr = "\r";

        $rawRequest = <<<'HTTP_REQUEST'
            GET /resource/path?id=1234#abc HTTP/1.1
            HTTP_REQUEST;

        $rawResponse = <<<HTTP_RESPONSE
            HTTP/1.1 200 OK$cr
            Content-Type: $mediaType$cr
            $cr
            This is some value that should not be displayed in the expected output.
            HTTP_RESPONSE;

        $expected = <<<EXPECTED
            GET /resource/path?id=1234#abc HTTP/1.1

            HTTP/1.1 200 OK$cr
            content-type: $mediaType$cr
            $cr
            $expectedResponseBody


            EXPECTED;

        $request = RequestSerializer::fromString($rawRequest);
        $response = ResponseSerializer::fromString($rawResponse);

        $formatter = new HttpExchangeFormatter();
        $formatted = $formatter->format($request, $response);

        $this->assertSame($expected, $formatted);
    }

    /**
     * @return array<array{mediaType: string, expectedResponseBody: string}>
     */
    public static function mediaTypeProvider(): array
    {
        return [
            ['mediaType' => 'image/jpeg', 'expectedResponseBody' => '[image data]'],
            ['mediaType' => 'application/foo+zip', 'expectedResponseBody' => '[archive data]'],
            ['mediaType' => 'application/x-gzip', 'expectedResponseBody' => '[archive data]'],
            ['mediaType' => 'audio/mp4', 'expectedResponseBody' => '[audio/video data]'],
            ['mediaType' => 'video/mp4', 'expectedResponseBody' => '[audio/video data]'],
            ['mediaType' => 'font/woff2', 'expectedResponseBody' => '[font data]'],
            ['mediaType' => 'application/pdf', 'expectedResponseBody' => '[PDF data]'],
        ];
    }

    public function testFormatWithBinaryMediaType(): void
    {
        $cr = "\r";
        $body = "This is some value that contains null bytes\0.\n";

        $rawRequest = <<<'HTTP_REQUEST'
            GET /resource/path?id=1234#abc HTTP/1.1
            HTTP_REQUEST;

        $rawResponse = <<<HTTP_RESPONSE
            HTTP/1.1 200 OK$cr
            Content-Type: application/octet-stream$cr
            $cr
            $body
            HTTP_RESPONSE;

        $expected = <<<EXPECTED
            GET /resource/path?id=1234#abc HTTP/1.1

            HTTP/1.1 200 OK$cr
            content-type: application/octet-stream$cr
            $cr
            [binary data]


            EXPECTED;

        $request = RequestSerializer::fromString($rawRequest);
        $response = ResponseSerializer::fromString($rawResponse);

        $formatter = new HttpExchangeFormatter();
        $formatted = $formatter->format($request, $response);

        $this->assertSame($expected, $formatted);
    }

    public function testFormatWithInvalidJson(): void
    {
        $cr = "\r";

        $rawRequest = <<<'HTTP_REQUEST'
            GET /resource/path?id=1234#abc HTTP/1.1
            HTTP_REQUEST;

        $rawResponse = <<<HTTP_RESPONSE
            HTTP/1.1 200 OK$cr
            Content-Type: application/vnd.something+json$cr
            $cr
            This JSON shouldn't parse.
            HTTP_RESPONSE;

        $expected = <<<EXPECTED
            GET /resource/path?id=1234#abc HTTP/1.1

            HTTP/1.1 200 OK$cr
            content-type: application/vnd.something+json$cr
            $cr
            This JSON shouldn't parse.


            EXPECTED;

        $request = RequestSerializer::fromString($rawRequest);
        $response = ResponseSerializer::fromString($rawResponse);

        $formatter = new HttpExchangeFormatter();
        $formatted = $formatter->format($request, $response);

        $this->assertSame($expected, $formatted);
    }
}
