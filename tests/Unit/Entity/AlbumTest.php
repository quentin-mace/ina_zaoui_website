<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Album;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    public function testIsTrue(): void
    {
        $album = new Album();

        $album->setName('test');

        $this->assertTrue('test' === $album->getName());
        $this->assertTrue(null === $album->getId());
    }

    public function testIsFalse(): void
    {
        $album = new Album();

        $album->setName('test');

        $this->assertFalse('false' === $album->getName());
        $this->assertFalse(is_int($album->getId()));
    }

    public function testIsEmpty(): void
    {
        $album = new Album();
        $album->setName('');

        $this->assertEmpty($album->getName());
        $this->assertEmpty($album->getId());
    }
}
