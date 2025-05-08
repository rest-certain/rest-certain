<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RestCertain\Internal\Type\JsonValue;
use RestCertain\Json\Json;
use stdClass;

class JsonValueTest extends TestCase
{
    /**
     * @param stdClass | bool | float | int | mixed[] | string | null $value
     */
    #[DataProvider('valueProvider')]
    public function testGetValue(stdClass | array | bool | float | int | string | null $value): void
    {
        $jsonValue = new JsonValue($value);

        $this->assertSame($value, $jsonValue->getValue());
    }

    /**
     * @param stdClass | bool | float | int | mixed[] | string | null $value
     */
    #[DataProvider('valueProvider')]
    public function testJsonSerialize(stdClass | array | bool | float | int | string | null $value): void
    {
        $jsonValue = new JsonValue($value);

        $this->assertJsonStringEqualsJsonString(Json::encode($value), (string) $jsonValue);
        $this->assertJsonStringEqualsJsonString(Json::encode($value), Json::encode($jsonValue));
    }

    /**
     * @return array<array{value: stdClass | bool | float | int | mixed[] | string | null}>
     */
    public static function valueProvider(): array
    {
        return [
            ['value' => (object) ['foo' => 'bar']],
            ['value' => ['foo', 'bar', 'baz']],
            ['value' => true],
            ['value' => false],
            ['value' => 1.23],
            ['value' => 123],
            ['value' => 'foo'],
            ['value' => null],
        ];
    }
}
