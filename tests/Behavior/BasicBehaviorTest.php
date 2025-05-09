<?php

declare(strict_types=1);

namespace RestCertain\Test\Behavior;

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
use function RestCertain\with;

final class BasicBehaviorTest extends TestCase
{
    use MockWebServer;

    public function testDelete(): void
    {
        $this->server()->addRoute(method: 'DELETE', uri: '/users/1', status: 204);

        delete('/users/{id}', ['id' => 1])
            ->then()->statusCode(204);
    }

    public function testGet(): void
    {
        $this->server()->addRoute(method: 'GET', uri: '/users/2', body: '{"id": 2, "name": "John"}');

        get('/users/{id}', ['id' => new Str('2')])
            ->then()->statusCode(200)
            ->and()->body('{"id": 2, "name": "John"}')
            ->and()->time(greaterThan(0), lessThan(1000));
    }

    public function testGiven(): void
    {
        $this->server()->addRoute(
            method: 'POST',
            uri: '/users',
            body: '{"id": 211, "name": "John Jacob Jingleheimer Schmidt"}',
        );

        given()->body('{"name": "John Jacob Jingleheimer Schmidt"}')
            ->when()->post('/users')
            ->then()->body('{"id": 211, "name": "John Jacob Jingleheimer Schmidt"}')
            ->and()->time(greaterThan(0), lessThan(1000));
    }

    public function testHead(): void
    {
        $this->server()->addRoute(method: 'HEAD', uri: '/users/3', headers: ['Content-Length' => '10']);

        head('/users/{id}', ['id' => '3'])
            ->then()->statusCode(200)
            ->and()->header('content-length', '10')
            ->and()->body('')
            ->and()->time(greaterThan(0), lessThan(1000));
    }

    public function testOptions(): void
    {
        $this->server()->addRoute(method: 'OPTIONS', uri: '/users/4', headers: ['X-Foo' => '123']);

        options('/users/{id}', ['id' => '4'])
            ->then()->statusCode(200)
            ->and()->header('x-foo', '123')
            ->and()->body('')
            ->and()->time(greaterThan(0), lessThan(1000));
    }

    public function testPatch(): void
    {
        $this->server()->addRoute(method: 'PATCH', uri: '/users/5', status: 202, body: '{"id": 5, "name": "Jane"}');

        patch('/users/{id}', ['id' => '5'])
            ->then()->statusCode(202)
            ->and()->body('{"id": 5, "name": "Jane"}')
            ->and()->time(greaterThan(0), lessThan(1000));
    }

    public function testPost(): void
    {
        $this->server()->addRoute(method: 'POST', uri: '/users', status: 201, body: '{"id": 6, "name": "Jill"}');

        post('/users')
            ->then()->statusCode(201)
            ->and()->body('{"id": 6, "name": "Jill"}')
            ->and()->time(greaterThan(0), lessThan(1000));
    }

    public function testPut(): void
    {
        $this->server()->addRoute(method: 'PUT', uri: '/users/7', status: 200, body: '{"id": 7, "name": "Jack"}');

        put('/users/{id}', ['id' => '7'])
            ->then()->statusCode(200)
            ->and()->body('{"id": 7, "name": "Jack"}')
            ->and()->time(greaterThan(0), lessThan(1000));
    }

    public function testRequest(): void
    {
        $this->server()->addRoute(method: 'get', uri: '/users', status: 303, body: 'See these other links');

        request('get', '/users')
            ->then()->statusCode(303)
            ->and()->body('See these other links')
            ->and()->time(greaterThan(0), lessThan(1000));
    }

    public function testWhen(): void
    {
        $this->server()->addRoute(
            method: 'GET',
            uri: '/users/8',
            body: '{"id": 8, "name": "Jane"}',
            headers: ['Set-Cookie' => 'abc=123', 'Content-Type' => 'application/json'],
        );

        when()->header('X-Foo', '123')
            ->and()->get('/users/{id}', ['id' => '8'])
            ->then()->statusCode(200)
            ->and()->cookie('abc', '123')
            ->and()->contentType('application/json')
            ->and()->body('{"id": 8, "name": "Jane"}')
            ->and()->time(greaterThan(0), lessThan(1000));
    }

    public function testWith(): void
    {
        $this->server()->addRoute(
            method: 'GET',
            uri: '/users/9',
            body: '{"id": 9, "name": "Jake"}',
        );

        with()->get('/users/{id}', ['id' => '9'])
            ->then()->body('{"id": 9, "name": "Jake"}')
            ->and()->time(greaterThan(0), lessThan(1000));
    }
}
