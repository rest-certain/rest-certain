<?php

declare(strict_types=1);

namespace Behavior;

use PHPUnit\Framework\TestCase;
use RestCertain\Test\MockWebServer;
use RestCertain\Test\Str;

use function RestCertain\Hamcrest\greaterThan;
use function RestCertain\Hamcrest\lessThan;
use function RestCertain\delete;
use function RestCertain\get;
use function RestCertain\given;
use function RestCertain\head;
use function RestCertain\options;
use function RestCertain\patch;
use function RestCertain\post;
use function RestCertain\put;
use function RestCertain\request;
use function RestCertain\when;
use function json_decode;

final class ExtractionTest extends TestCase
{
    use MockWebServer;

    public function testDelete(): void
    {
        $this->server()->addRoute(method: 'DELETE', uri: '/users/1', status: 204);

        $extracted = delete('/users/{id}', ['id' => 1])
            ->then()->statusCode(204)
            ->extract()->statusCode();

        $this->assertSame(204, $extracted);
    }

    public function testGet(): void
    {
        $this->server()->addRoute(method: 'GET', uri: '/users/2', body: '{"id": 2, "name": "John"}');

        $extracted = get('/users/{id}', ['id' => new Str('2')])
            ->then()->statusCode(200)
            ->and()->body('{"id": 2, "name": "John"}')
            ->and()->time(greaterThan(0), lessThan(1000))
            ->extract()->path('name');

        $this->assertSame('John', $extracted);
    }

    public function testGiven(): void
    {
        $this->server()->addRoute(
            method: 'POST',
            uri: '/users',
            body: '{"id": 211, "name": "John Jacob Jingleheimer Schmidt"}',
        );

        $extracted = given()->body('{"name": "John Jacob Jingleheimer Schmidt"}')
            ->when()->post('/users')
            ->then()->body('{"id": 211, "name": "John Jacob Jingleheimer Schmidt"}')
            ->and()->time(greaterThan(0), lessThan(1000))
            ->extract()->time();

        $this->assertGreaterThan(0, $extracted);
        $this->assertLessThan(1000, $extracted);
    }

    public function testHead(): void
    {
        $this->server()->addRoute(method: 'HEAD', uri: '/users/3', headers: ['Content-Length' => '10']);

        $extracted = head('/users/{id}', ['id' => '3'])
            ->then()->statusCode(200)
            ->and()->header('content-length', '10')
            ->and()->body('')
            ->and()->time(greaterThan(0), lessThan(1000))
            ->extract()->headers();

        $this->assertArrayHasKey('content-length', $extracted);
        $this->assertSame(['10'], $extracted['content-length']);
    }

    public function testOptions(): void
    {
        $this->server()->addRoute(method: 'OPTIONS', uri: '/users/4', headers: ['X-Foo' => '123']);

        $extracted = options('/users/{id}', ['id' => '4'])
            ->then()->statusCode(200)
            ->and()->header('x-foo', '123')
            ->and()->body('')
            ->and()->time(greaterThan(0), lessThan(1000))
            ->extract()->header('x-foo');

        $this->assertSame(['123'], $extracted);
    }

    public function testPatch(): void
    {
        $this->server()->addRoute(method: 'PATCH', uri: '/users/5', status: 202, body: '{"id": 5, "name": "Jane"}');

        $extracted = patch('/users/{id}', ['id' => '5'])
            ->then()->statusCode(202)
            ->and()->body('{"id": 5, "name": "Jane"}')
            ->and()->time(greaterThan(0), lessThan(1000))
            ->extract()->body()->path('@');

        $this->assertEquals(json_decode('{"id": 5, "name": "Jane"}'), $extracted);
    }

    public function testPost(): void
    {
        $this->server()->addRoute(method: 'POST', uri: '/users', status: 201, body: '{"id": 6, "name": "Jill"}');

        $extracted = post('/users')
            ->then()->statusCode(201)
            ->and()->body('{"id": 6, "name": "Jill"}')
            ->and()->time(greaterThan(0), lessThan(1000))
            ->extract()->body()->asPrettyString();

        $this->assertSame(
            <<<'JSON'
            {
                "id": 6,
                "name": "Jill"
            }
            JSON,
            $extracted,
        );
    }

    public function testPut(): void
    {
        $this->server()->addRoute(method: 'PUT', uri: '/users/7', status: 200, body: '{"id": 7, "name": "Jack"}');

        $extracted = put('/users/{id}', ['id' => '7'])
            ->then()->statusCode(200)
            ->and()->body('{"id": 7, "name": "Jack"}')
            ->and()->time(greaterThan(0), lessThan(1000))
            ->extract()->body()->asPsrStream();

        $this->assertSame('{"id": 7, "name": "Jack"}', (string) $extracted);
    }

    public function testRequest(): void
    {
        $this->server()->addRoute(method: 'get', uri: '/users', status: 303, body: 'See these other links');

        $extracted = request('get', '/users')
            ->then()->statusCode(303)
            ->and()->body('See these other links')
            ->and()->time(greaterThan(0), lessThan(1000))
            ->extract()->body()->asString();

        $this->assertSame('See these other links', $extracted);
    }

    public function testWhen(): void
    {
        $this->server()->addRoute(
            method: 'GET',
            uri: '/users/8',
            body: '{"id": 8, "name": "Jane"}',
            headers: ['Set-Cookie' => 'abc=123', 'Content-Type' => 'application/json'],
        );

        $extracted = when()->header('X-Foo', '123')
            ->and()->get('/users/{id}', ['id' => '8'])
            ->then()->statusCode(200)
            ->and()->cookie('abc', '123')
            ->and()->contentType('application/json')
            ->and()->body('{"id": 8, "name": "Jane"}')
            ->and()->time(greaterThan(0), lessThan(1000))
            ->extract()->cookie('abc');

        $this->assertSame('123', $extracted);
    }
}
