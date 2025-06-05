<?php

declare(strict_types=1);

namespace RestCertain\Test\Behavior;

use PHPUnit\Framework\TestCase;
use RestCertain\Test\MockWebServer;

use function RestCertain\given;

class HeadersTest extends TestCase
{
    use MockWebServer;

    public function testHeadersCookiesStatusEtc(): void
    {
        $this->server()->addRoute(method: 'PUT', uri: '/something/123?foo=bar', body: [
            'id' => 123,
            'name' => 'Something Cool',
        ], headers: [
            'Content-Type' => 'application/json',
            'Set-Cookie' => ['foo=bar; Path=/; HttpOnly', 'baz=qux; Path=/; Secure'],
        ]);

        given()
            ->accept('application/json')
            ->queryParam('foo', 'bar')
            ->and()->body(['name' => 'Something Cool'])
        ->when()
            ->put('/something/{id}', ['id' => 123])
        ->then()
            ->statusCode(200)
            ->and()->header('content-type', 'application/json')
            ->and()->cookie('foo', 'bar')
            ->and()->cookie('baz', 'qux')
            ->and()->statusLine('HTTP/1.1 200 OK')
            ->and()->path('id', 123);
    }
}
