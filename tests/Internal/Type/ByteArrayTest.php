<?php

declare(strict_types=1);

namespace RestCertain\Test\Internal\Type;

use PHPUnit\Framework\TestCase;
use RestCertain\Internal\Type\ByteArray;

class ByteArrayTest extends TestCase
{
    public function testByteArray(): void
    {
        $byteArray = new ByteArray('foo');
        $bytes = [];

        foreach ($byteArray as $byte) {
            $bytes[] = $byte;
        }

        $this->assertSame(['f', 'o', 'o'], $bytes);
    }

    public function testToString(): void
    {
        $byteArray = new ByteArray('foo');

        $this->assertSame('foo', (string) $byteArray);
    }

    public function testGetValue(): void
    {
        $byteArray = new ByteArray('foo');

        $this->assertSame('foo', $byteArray->getValue());
    }

    public function testKey(): void
    {
        $byteArray = new ByteArray('foo');

        $this->assertTrue($byteArray->valid());
        $this->assertSame(0, $byteArray->key());
        $this->assertSame('f', $byteArray->current());

        $byteArray->next();
        $this->assertTrue($byteArray->valid());
        $this->assertSame(1, $byteArray->key());
        $this->assertSame('o', $byteArray->current());

        $byteArray->next();
        $this->assertTrue($byteArray->valid());
        $this->assertSame(2, $byteArray->key());
        $this->assertSame('o', $byteArray->current());

        $byteArray->next();
        $this->assertFalse($byteArray->valid());
    }
}
