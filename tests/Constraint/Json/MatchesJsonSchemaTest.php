<?php

declare(strict_types=1);

namespace RestCertain\Test\Constraint\Json;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use RestCertain\Constraint\Json\MatchesJsonSchema;
use RestCertain\Exception\JsonSchemaFailure;
use RestCertain\Exception\MissingConfiguration;
use RestCertain\Exception\RequestFailed;
use RestCertain\RestCertain;
use RestCertain\Test\MockWebServer;
use RuntimeException;

use function assert;
use function file_get_contents;
use function is_array;
use function is_object;
use function json_decode;

class MatchesJsonSchemaTest extends TestCase
{
    use MockeryPHPUnitIntegration;
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
    }

    public function testMatchesJsonSchemaFromUriString(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/complex-object.json');
        $baseUrl = $this->bypass->getBaseUrl();

        $this->bypass->addRoute(method: 'GET', uri: '/schema.json', body: $schema);

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

        $this->assertThat($testValue, MatchesJsonSchema::fromUri($baseUrl . '/schema.json'));
    }

    public function testMatchesJsonSchemaFromUriObject(): void
    {
        $schema = (string) file_get_contents(__DIR__ . '/fixtures/complex-object.json');
        $baseUrl = $this->bypass->getBaseUrl();

        $this->bypass->addRoute(method: 'GET', uri: '/schema.json', body: $schema);

        $testValue = [
            'name' => 'John Doe',
            'age' => 25,
        ];

        $uri = new Uri($baseUrl . '/schema.json');

        $this->assertThat($testValue, MatchesJsonSchema::fromUri($uri));
    }

    public function testFromFileThrowsWhenFileNotFound(): void
    {
        $this->expectException(JsonSchemaFailure::class);
        $this->expectExceptionMessage('Failed to read file');

        $this->assertThat('foo', MatchesJsonSchema::fromFile(__DIR__ . '/this-file-does-not-exist.json'));
    }

    public function testFromUriWhenHttpClientOrRequestFactoryNotSet(): void
    {
        RestCertain::$config = null;

        $this->expectException(MissingConfiguration::class);
        $this->expectExceptionMessage(
            'Unable to create a JSON Schema matcher from a URI without an HTTP client or request factory. Set the HTTP '
            . 'client and request factory on RestCertain::$config or pass them to this method.',
        );

        MatchesJsonSchema::fromUri('https://example.com/schema.json');
    }

    public function testFromUriWhenHttpClientThrowsAnException(): void
    {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $uri = new Uri('https://example.com/schema.json');

        $exception = new class extends RuntimeException implements ClientExceptionInterface {
            /** @var string */
            // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
            protected $message = 'HTTP was naughty. Bad HTTP!';
        };

        $requestFactory
            ->expects('createRequest')
            ->with('GET', $uri)
            ->andReturns($request);

        $httpClient
            ->expects('sendRequest')
            ->with($request)
            ->andThrows($exception);

        $constraint = MatchesJsonSchema::fromUri(
            uri: $uri,
            httpClient: $httpClient,
            requestFactory: $requestFactory,
        );

        $this->expectException(RequestFailed::class);
        $this->expectExceptionMessage('HTTP was naughty. Bad HTTP!');

        $this->assertThat('foo', $constraint);
    }

    public function testMatchesJsonSchemaFromUriWhenResponseIsNotOk(): void
    {
        $baseUrl = $this->bypass->getBaseUrl();

        $this->bypass->addRoute(method: 'GET', uri: '/schema.json', status: 400, body: 'You made a bad request!');

        $this->expectException(RequestFailed::class);
        $this->expectExceptionMessage(
            "HTTP request failed with status code '400' and response body:\n\nYou made a bad request!\n",
        );

        $this->assertThat('foo', MatchesJsonSchema::fromUri($baseUrl . '/schema.json'));
    }

    public function testMatchesJsonSchemaFromUriWhenResponseHasNoBody(): void
    {
        $baseUrl = $this->bypass->getBaseUrl();

        $this->bypass->addRoute(method: 'GET', uri: '/schema.json', body: '');

        $this->expectException(RequestFailed::class);
        $this->expectExceptionMessage("HTTP request failed with status code '200' and no response body");

        $this->assertThat('foo', MatchesJsonSchema::fromUri($baseUrl . '/schema.json'));
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
        $this->assertThat($testValue2, $constraint);
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
}
