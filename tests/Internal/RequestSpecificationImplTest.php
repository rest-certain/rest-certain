<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use RestCertain\Config;
use RestCertain\Internal\RequestSpecificationImpl;
use RestCertain\Test\Json;
use RestCertain\Test\Str;
use SplTempFileObject;

class RequestSpecificationImplTest extends TestCase
{
    private Psr17Factory $factory;
    private RequestSpecificationImpl $spec;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
        $this->spec = new RequestSpecificationImpl(new Config());
    }

    public function testAccept(): void
    {
        $this->assertSame($this->spec, $this->spec->accept('application/json'));
        $this->assertSame($this->spec, $this->spec->accept(new Str('application/xml')));
    }

    public function testAnd(): void
    {
        $this->assertSame($this->spec, $this->spec->and());
    }

    public function testAuth(): void
    {
        $this->markTestIncomplete();
    }

    public function testBasePath(): void
    {
        $this->assertSame($this->spec, $this->spec->basePath('/foo'));
        $this->assertSame($this->spec, $this->spec->basePath(new Str('/bar')));
    }

    public function testBaseUri(): void
    {
        $this->assertSame($this->spec, $this->spec->baseUri('https://api.example.net/foo'));
        $this->assertSame($this->spec, $this->spec->baseUri($this->factory->createUri('https://api.example.net/bar')));
        $this->assertSame($this->spec, $this->spec->baseUri(new Str('https://api.example.net/baz')));
    }

    public function testBody(): void
    {
        $this->assertSame($this->spec, $this->spec->body('{"foo": "bar"}'));
        $this->assertSame($this->spec, $this->spec->body(new Str('{"foo": "bar"}')));
        $this->assertSame($this->spec, $this->spec->body(new Json(['foo' => 'bar'])));
        $this->assertSame($this->spec, $this->spec->body($this->factory->createStream('{"foo": "bar"}')));

        $file = new SplTempFileObject();
        $file->fwrite('{"foo": "bar"}');
        $this->assertSame($this->spec, $this->spec->body($file));
    }

    public function testContentType(): void
    {
        $this->assertSame($this->spec, $this->spec->contentType('application/json'));
        $this->assertSame($this->spec, $this->spec->contentType(new Str('application/xml')));
    }

    public function testCookie(): void
    {
        $this->assertSame($this->spec, $this->spec->cookie('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->cookie('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->cookie('quux', ''));
        $this->assertSame($this->spec, $this->spec->cookie('corge'));
    }

    public function testCookies(): void
    {
        $this->assertSame($this->spec, $this->spec->cookies([
            'foo' => 'bar',
            'baz' => new Str('qux'),
            'quux' => '',
            'corge' => null,
        ]));
    }

    public function testDelete(): void
    {
        $this->markTestIncomplete();
    }

    public function testExpect(): void
    {
        $this->markTestIncomplete();
    }

    public function testFormParam(): void
    {
        $this->assertSame($this->spec, $this->spec->formParam('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->formParam('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->formParam('quux', 'corge', new Str('grault')));
        $this->assertSame($this->spec, $this->spec->formParam('grault', 'garply', 'waldo', new Str('fred')));
        $this->assertSame($this->spec, $this->spec->formParam('foo', 'plugh', 'xyzzy'));
        $this->assertSame($this->spec, $this->spec->formParam('thud', ''));
    }

    public function testFormParams(): void
    {
        $this->assertSame($this->spec, $this->spec->formParams([
            'foo' => ['bar', new Str('baz')],
            'qux' => new Str('quux'),
            'corge' => 'grault',
            'garply' => '',
        ]));
    }

    public function testGet(): void
    {
        $this->markTestIncomplete();
    }

    public function testGiven(): void
    {
        $this->assertSame($this->spec, $this->spec->given());
    }

    public function testHead(): void
    {
        $this->markTestIncomplete();
    }

    public function testHeader(): void
    {
        $this->assertSame($this->spec, $this->spec->header('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->header('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->header('quux', 'corge', new Str('grault'), 'garply'));
        $this->assertSame($this->spec, $this->spec->header('foo', 'waldo'));
    }

    public function testHeaders(): void
    {
        $this->assertSame($this->spec, $this->spec->headers([
            'foo' => 'bar',
            'baz' => new Str('qux'),
            'quux' => ['corge', new Str('grault'), 'garply'],
        ]));
    }

    public function testOptions(): void
    {
        $this->markTestIncomplete();
    }

    public function testParam(): void
    {
        $this->assertSame($this->spec, $this->spec->param('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->param('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->param('quux', 'corge', new Str('grault')));
        $this->assertSame($this->spec, $this->spec->param('grault', 'garply', 'waldo', new Str('fred')));
        $this->assertSame($this->spec, $this->spec->param('foo', 'plugh', 'xyzzy'));
        $this->assertSame($this->spec, $this->spec->param('thud', ''));
    }

    public function testParams(): void
    {
        $this->assertSame($this->spec, $this->spec->params([
            'foo' => ['bar', new Str('baz')],
            'qux' => new Str('quux'),
            'corge' => 'grault',
            'garply' => '',
        ]));
    }

    public function testPatch(): void
    {
        $this->markTestIncomplete();
    }

    public function testPathParam(): void
    {
        $this->assertSame($this->spec, $this->spec->pathParam('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->pathParam('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->pathParam('quux', 123));
    }

    public function testPathParams(): void
    {
        $this->assertSame($this->spec, $this->spec->pathParams([
            'foo' => 'bar',
            'baz' => new Str('qux'),
            'quux' => 123,
        ]));
    }

    public function testPort(): void
    {
        $this->assertSame($this->spec, $this->spec->port(8080));
    }

    public function testPost(): void
    {
        $this->markTestIncomplete();
    }

    public function testPut(): void
    {
        $this->markTestIncomplete();
    }

    public function testQueryParam(): void
    {
        $this->assertSame($this->spec, $this->spec->queryParam('foo', 'bar'));
        $this->assertSame($this->spec, $this->spec->queryParam('baz', new Str('qux')));
        $this->assertSame($this->spec, $this->spec->queryParam('quux', 'corge', new Str('grault')));
        $this->assertSame($this->spec, $this->spec->queryParam('grault', 'garply', 'waldo', new Str('fred')));
        $this->assertSame($this->spec, $this->spec->queryParam('foo', 'plugh', 'xyzzy'));
        $this->assertSame($this->spec, $this->spec->queryParam('thud', ''));
    }

    public function testQueryParams(): void
    {
        $this->assertSame($this->spec, $this->spec->queryParams([
            'foo' => ['bar', new Str('baz')],
            'qux' => new Str('quux'),
            'corge' => 'grault',
            'garply' => '',
        ]));
    }

    public function testRedirects(): void
    {
        $this->markTestIncomplete();
    }

    public function testRequest(): void
    {
        $this->markTestIncomplete();
    }

    public function testResponse(): void
    {
        $this->markTestIncomplete();
    }

    public function testSetResponseSpecification(): void
    {
        $this->markTestIncomplete();
    }

    public function testThat(): void
    {
        $this->assertSame($this->spec, $this->spec->that());
    }

    public function testThen(): void
    {
        $this->markTestIncomplete();
    }

    public function testWhen(): void
    {
        $this->assertSame($this->spec, $this->spec->when());
    }

    public function testWith(): void
    {
        $this->assertSame($this->spec, $this->spec->with());
    }
}
