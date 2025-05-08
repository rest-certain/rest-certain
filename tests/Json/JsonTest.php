<?php

declare(strict_types=1);

namespace RestCertain\Test\Json;

use PHPUnit\Framework\TestCase;
use RestCertain\Exception\UnableToDecodeJson;
use RestCertain\Exception\UnableToEncodeJson;
use RestCertain\Json\Json;

use const NAN;

class JsonTest extends TestCase
{
    public function testEncode(): void
    {
        $this->assertSame('{"foo":"bar/baz ☃"}', Json::encode(['foo' => 'bar/baz ☃']));
    }

    public function testEncodePretty(): void
    {
        $expected = <<<'JSON'
            {
                "foo": "bar/baz ☃"
            }
            JSON;

        $this->assertSame($expected, Json::encode(['foo' => 'bar/baz ☃'], true));
    }

    public function testEncodeThrowsException(): void
    {
        $this->expectException(UnableToEncodeJson::class);
        $this->expectExceptionMessage('Failed to encode value to JSON: Inf and NaN cannot be JSON encoded');

        Json::encode(NAN);
    }

    public function testDecode(): void
    {
        $json = "{\"foo\":9223372036854775808,\"bar\":\"bad unicode a\xd0\xf2b\"}";

        $this->assertEquals(
            (object) [
                'foo' => '9223372036854775808',
                'bar' => "bad unicode a\u{fffd}\u{fffd}b",
            ],
            Json::decode($json),
        );
    }

    public function testDecodeThrowsException(): void
    {
        $this->expectException(UnableToDecodeJson::class);
        $this->expectExceptionMessage('Failed to decode value as JSON: Syntax error');

        Json::decode('{"foo":');
    }
}
