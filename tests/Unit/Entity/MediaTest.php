<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaTest extends TestCase
{
    private string $tempFile;
    private string $tempFile2;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'media_test_');
        $this->tempFile2 = tempnam(sys_get_temp_dir(), 'media_test_');
    }

    protected function tearDown(): void
    {
        foreach ([$this->tempFile, $this->tempFile2] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testIsTrue(): void
    {
        $media = new Media();
        $user = new User();
        $album = new Album();
        $file = new UploadedFile($this->tempFile, 'test.jpg');

        $media->setPath('public/uploads/0001.jpg');
        $media->setTitle('test');
        $media->setUser($user);
        $media->setAlbum($album);
        $media->setFile($file);

        $this->assertTrue('public/uploads/0001.jpg' === $media->getPath());
        $this->assertTrue('test' === $media->getTitle());
        $this->assertTrue($media->getUser() === $user);
        $this->assertTrue($media->getAlbum() === $album);
        $this->assertTrue(null === $media->getId());
        $this->assertTrue($media->getFile() === $file);
    }

    public function testIsFalse(): void
    {
        $media = new Media();
        $user = new User();
        $album = new Album();
        $file = new UploadedFile($this->tempFile, 'test.jpg');

        $media->setPath('public/uploads/0001.jpg');
        $media->setTitle('test');
        $media->setUser($user);
        $media->setAlbum($album);
        $media->setFile($file);

        $this->assertFalse('false' === $media->getPath());
        $this->assertFalse('false' === $media->getTitle());
        $this->assertFalse($media->getUser() === new User());
        $this->assertFalse($media->getAlbum() === new Album());
        $this->assertFalse(is_int($media->getId()));
        $this->assertFalse($media->getFile() === new UploadedFile($this->tempFile2, 'test2.jpg'));
    }

    public function testIsEmpty(): void
    {
        $media = new Media();
        $media->setPath('');
        $media->setTitle('');

        $this->assertEmpty($media->getPath());
        $this->assertEmpty($media->getTitle());
        $this->assertEmpty($media->getId());
        $this->assertEmpty($media->getUser());
        $this->assertEmpty($media->getAlbum());
        $this->assertEmpty($media->getFile());
    }
}