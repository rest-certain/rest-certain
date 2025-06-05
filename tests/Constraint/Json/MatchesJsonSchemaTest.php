<?php

declare(strict_types=1);

namespace RestCertain\Test\Constraint\Json;

use Nyholm\Psr7\Uri;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use RestCertain\Constraint\Json\MatchesJsonSchema;
use RestCertain\Exception\JsonSchemaFailure;
use RestCertain\Exception\MissingConfiguration;
use RestCertain\RestCertain;
use RestCertain\Test\MockWebServer;

use function assert;
use function file_get_contents;
use function is_array;
use function is_object;
use function json_decode;
use function json_encode;

class MatchesJsonSchemaTest extends TestCase
{
    use MockWebServer;

    public function testMatchesJsonSchemaFromString(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/typical-minimum.json');

        $testValue = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'age' => 21,
        ];

        $this->assertThat($testValue, MatchesJsonSchema::fromString($schema));
        $this->assertThat(json_encode($testValue), MatchesJsonSchema::fromString($schema));
    }

    public function testMatchesJsonSchemaFromFile(): void
    {
        $testValue = [
            'fruits' => ['apple', 'orange', 'pear'],
            'vegetables' => [
                [
                    'veggieName' => 'potato',
                    'veggieLike' => true,
                ],
                [
                    'veggieName' => 'broccoli',
                    'veggieLike' => false,
                ],
            ],
        ];

        $this->assertThat($testValue, MatchesJsonSchema::fromFile(__DIR__ . '/fixtures/arrays-of-things.json'));
        $this->assertThat(
            json_encode($testValue),
            MatchesJsonSchema::fromFile(__DIR__ . '/fixtures/arrays-of-things.json'),
        );
    }

    public function testMatchesJsonSchemaFromDataAsObject(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/enumerated-values.json');
        $schemaDecoded = json_decode($schema);

        assert(is_object($schemaDecoded));

        $testValue = [
            'data' => [1, 2, 3],
        ];

        $this->assertThat($testValue, MatchesJsonSchema::fromData($schemaDecoded));
        $this->assertThat(json_encode($testValue), MatchesJsonSchema::fromData($schemaDecoded));
    }

    public function testMatchesJsonSchemaFromDataAsArray(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/enumerated-values.json');

        /** @var array<string, mixed> $schemaDecoded */
        $schemaDecoded = json_decode($schema, true);

        assert(is_array($schemaDecoded));

        $testValue = [
            'data' => 42,
        ];

        $this->assertThat($testValue, MatchesJsonSchema::fromData($schemaDecoded));
        $this->assertThat(json_encode($testValue), MatchesJsonSchema::fromData($schemaDecoded));
    }

    public function testMatchesJsonSchemaFromUriString(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/complex-object.json');
        $baseUrl = $this->server()->getBaseUrl();

        $this->server()->addRoute(method: 'GET', uri: '/complex-object.schema.json', body: $schema);

        $testValue = [
            'name' => 'John Doe',
            'age' => 25,
            'address' => [
                'street' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postalCode' => '10001',
            ],
            'hobbies' => ['reading', 'running'],
        ];

        $this->assertThat($testValue, MatchesJsonSchema::fromUri($baseUrl . '/complex-object.schema.json'));
        $this->assertThat(
            json_encode($testValue),
            MatchesJsonSchema::fromUri($baseUrl . '/complex-object.schema.json'),
        );
    }

    public function testMatchesJsonSchemaFromUriObject(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/conditional-validation-dependentRequired.json');
        $baseUrl = $this->server()->getBaseUrl();

        $this->server()->addRoute(
            method: 'GET',
            uri: '/conditional-validation-dependentRequired.schema.json',
            body: $schema,
        );

        $testValue = [
            'foo' => true,
            'bar' => 'Hello World',
        ];

        $uri = new Uri($baseUrl . '/conditional-validation-dependentRequired.schema.json');

        $this->assertThat($testValue, MatchesJsonSchema::fromUri($uri));
        $this->assertThat(json_encode($testValue), MatchesJsonSchema::fromUri($uri));
    }

    public function testFromFileThrowsWhenFileNotFound(): void
    {
        $this->expectException(JsonSchemaFailure::class);
        $this->expectExceptionMessage('Failed to read file');

        $this->assertThat('foo', MatchesJsonSchema::fromFile(__DIR__ . '/this-file-does-not-exist.json'));
    }

    public function testFromUriWhenUriIsInvalid(): void
    {
        $this->expectException(JsonSchemaFailure::class);
        $this->expectExceptionMessage('Invalid JSON Schema URI: foo bar');

        MatchesJsonSchema::fromUri('foo bar');
    }

    public function testMatchesJsonSchemaWithConditionalIfElse(): void
    {
        $testValue1 = [
            'isMember' => true,
            'membershipNumber' => '1234567890',
        ];

        $testValue2 = [
            'isMember' => false,
            'membershipNumber' => 'GUEST1234567890',
        ];

        $constraint = MatchesJsonSchema::fromFile(__DIR__ . '/fixtures/conditional-if-else.json');

        $this->assertThat($testValue1, $constraint);
        $this->assertThat(json_encode($testValue1), $constraint);
        $this->assertThat($testValue2, $constraint);
        $this->assertThat(json_encode($testValue2), $constraint);
    }

    public function testFailureWithConditionalIfElse(): void
    {
        $testValue = [
            'isMember' => true,
            'membershipNumber' => '123456789',
        ];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('matches JSON schema');
        $this->expectExceptionMessage('Found the following JSON Schema validation errors:');
        $this->expectExceptionMessage('/membershipNumber:');
        $this->expectExceptionMessage('Minimum string length is 10, found 9');

        $this->assertThat($testValue, MatchesJsonSchema::fromFile(__DIR__ . '/fixtures/conditional-if-else.json'));
    }

    public function testFailureWithConditionalIfElseWhenTestValueIsString(): void
    {
        $testValue = [
            'isMember' => true,
            'membershipNumber' => '123456789',
        ];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('matches JSON schema');
        $this->expectExceptionMessage('Found the following JSON Schema validation errors:');
        $this->expectExceptionMessage('/membershipNumber:');
        $this->expectExceptionMessage('Minimum string length is 10, found 9');

        $this->assertThat(
            json_encode($testValue),
            MatchesJsonSchema::fromFile(__DIR__ . '/fixtures/conditional-if-else.json'),
        );
    }

    public function testFailureWithMultipleErrors(): void
    {
        $testValue = [
            'name' => 1234,
            'age' => 'foo',
            'address' => [
                'street' => 1234,
                'postalCode' => '1234',
            ],
            'hobbies' => 'something cool',
        ];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('matches JSON schema');
        $this->expectExceptionMessage('Found the following JSON Schema validation errors:');
        $this->expectExceptionMessage('/name:');
        $this->expectExceptionMessage('The data (integer) must match the type: string');
        $this->expectExceptionMessage('/age:');
        $this->expectExceptionMessage('The data (string) must match the type: integer');
        $this->expectExceptionMessage('/address:');
        $this->expectExceptionMessage('The required properties (city, state) are missing');
        $this->expectExceptionMessage('/address/street:');
        $this->expectExceptionMessage('The data (integer) must match the type: string');
        $this->expectExceptionMessage('/address/postalCode:');
        $this->expectExceptionMessage('The data (integer) must match the type: string');
        $this->expectExceptionMessage('/hobbies:');
        $this->expectExceptionMessage('The data (string) must match the type: array');

        $this->assertThat($testValue, MatchesJsonSchema::fromFile(__DIR__ . '/fixtures/complex-object.json'));
    }

    public function testFailureWithMultipleErrorsWhenTestValueIsString(): void
    {
        $testValue = [
            'name' => 1234,
            'age' => 'foo',
            'address' => [
                'street' => 1234,
                'postalCode' => '1234',
            ],
            'hobbies' => 'something cool',
        ];

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('matches JSON schema');
        $this->expectExceptionMessage('Found the following JSON Schema validation errors:');
        $this->expectExceptionMessage('/name:');
        $this->expectExceptionMessage('The data (integer) must match the type: string');
        $this->expectExceptionMessage('/age:');
        $this->expectExceptionMessage('The data (string) must match the type: integer');
        $this->expectExceptionMessage('/address:');
        $this->expectExceptionMessage('The required properties (city, state) are missing');
        $this->expectExceptionMessage('/address/street:');
        $this->expectExceptionMessage('The data (integer) must match the type: string');
        $this->expectExceptionMessage('/address/postalCode:');
        $this->expectExceptionMessage('The data (integer) must match the type: string');
        $this->expectExceptionMessage('/hobbies:');
        $this->expectExceptionMessage('The data (string) must match the type: array');

        $this->assertThat(
            json_encode($testValue),
            MatchesJsonSchema::fromFile(__DIR__ . '/fixtures/complex-object.json'),
        );
    }

    public function testMatchesJsonSchemaThrowsExceptionWhenConfigNotSet(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/typical-minimum.json');
        $testValue = ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 21];
        RestCertain::$config = null;

        $this->expectException(MissingConfiguration::class);
        $this->expectExceptionMessage('No JSON Schema validator found. Please configure a JSON Schema validator.');

        $this->assertThat($testValue, MatchesJsonSchema::fromString($schema));
    }
}
