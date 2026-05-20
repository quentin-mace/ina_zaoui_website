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

        $this->assertTrue($album->getName() === 'test');
        $this->assertTrue($album->getId() === null);
    }

    public function testIsFalse(): void
    {
        $album = new Album();

        $album->setName('test');

        $this->assertFalse($album->getName() === 'false');
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
