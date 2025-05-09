<?php

declare(strict_types=1);

namespace RestCertain\Test\Json\Schema;

use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use RestCertain\Test\MockWebServer;
use RestCertain\Test\Str;

use function RestCertain\Hamcrest\assertThat;
use function RestCertain\Json\Schema\matchesJsonSchema;
use function RestCertain\Json\Schema\matchesJsonSchemaFromData;
use function RestCertain\Json\Schema\matchesJsonSchemaFromFile;
use function RestCertain\Json\Schema\matchesJsonSchemaFromUri;
use function assert;
use function file_get_contents;
use function is_array;
use function is_object;
use function json_decode;

class MatchersTest extends TestCase
{
    use MockWebServer;

    public function testMatchesJsonSchema(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/minimum.json');
        $testValue = ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 21];

        assertThat($testValue, matchesJsonSchema($schema));
    }

    public function testMatchesJsonSchemaUsingStringable(): void
    {
        $contents = (string) file_get_contents(__DIR__ . '/fixtures/minimum.json');
        $schema = new Str($contents);
        $testValue = ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 21];

        assertThat($testValue, matchesJsonSchema($schema));
    }

    public function testMatchesJsonSchemaDataAsObject(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/minimum.json');
        $testValue = ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 21];

        /** @var array<string, mixed> $schemaData */
        $schemaData = json_decode($schema);
        assert(is_object($schemaData));

        assertThat($testValue, matchesJsonSchemaFromData($schemaData));
    }

    public function testMatchesJsonSchemaDataAsArray(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/minimum.json');
        $testValue = ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 21];

        /** @var array<string, mixed> $schemaData */
        $schemaData = json_decode($schema, true);
        assert(is_array($schemaData));

        assertThat($testValue, matchesJsonSchemaFromData($schemaData));
    }

    public function testMatchesJsonSchemaFromFile(): void
    {
        $filename = __DIR__ . '/fixtures/minimum.json';
        $testValue = ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 21];

        assertThat($testValue, matchesJsonSchemaFromFile($filename));
    }

    public function testMatchesJsonSchemaFromFileUsingStringable(): void
    {
        $filename = new Str(__DIR__ . '/fixtures/minimum.json');
        $testValue = ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 21];

        assertThat($testValue, matchesJsonSchemaFromFile($filename));
    }

    public function testMatchesJsonSchemaFromUri(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/minimum.json');
        $url = new Uri($this->server()->getBaseUrl() . '/schema.json');

        $this->server()->addRoute(method: 'GET', uri: '/schema.json', body: $schema);

        assertThat(['firstName' => 'John', 'lastName' => 'Doe', 'age' => 21], matchesJsonSchemaFromUri($url));
        assertThat(['firstName' => 'Jane', 'lastName' => 'Smith', 'age' => 42], matchesJsonSchemaFromUri($url));
    }

    public function testMatchesJsonSchemaFromUriUsingString(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/minimum.json');
        $url = $this->server()->getBaseUrl() . '/schema.json';
        $testValue = ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 21];

        $this->server()->addRoute(method: 'GET', uri: '/schema.json', body: $schema);

        assertThat($testValue, matchesJsonSchemaFromUri($url));
    }

    public function testMatchesJsonSchemaFromUriUsingStringable(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/minimum.json');
        $url = new Str($this->server()->getBaseUrl() . '/schema.json');
        $testValue = ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 21];

        $this->server()->addRoute(method: 'GET', uri: '/schema.json', body: $schema);

        assertThat($testValue, matchesJsonSchemaFromUri($url));
    }
}
