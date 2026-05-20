<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testIsTrue(): void
    {
        $user = new User();
        $media = new Media();
        $collection = $user->getMedias();
        $collection->add($media);

        $user->setEmail('test@test.com');
        $user->setPassword('test');
        $user->setAdmin(true);
        $user->setName('test');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setDescription('test');
        $user->setHasAccess(true);
        $user->setMedias($collection);

        $this->assertTrue('test@test.com' === $user->getEmail());
        $this->assertTrue('test' === $user->getPassword());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue('test' === $user->getName());
        $this->assertTrue($user->getRoles() === ['ROLE_ADMIN', 'ROLE_USER']);
        $this->assertTrue('test' === $user->getDescription());
        $this->assertTrue($user->hasAccess());
        $this->assertTrue($user->getMedias()->contains($media));
        $this->assertTrue(null === $user->getId());
        $this->assertTrue('test@test.com' === $user->getUserIdentifier());
    }

    public function testIsFalse(): void
    {
        $user = new User();
        $media = new Media();
        $collection = $user->getMedias();
        $collection->add($media);

        $user->setEmail('test@test.com');
        $user->setPassword('test');
        $user->setAdmin(false);
        $user->setName('test');
        $user->setRoles([]);
        $user->setDescription('test');
        $user->setHasAccess(false);
        $user->setMedias($collection);

        $this->assertFalse('false' === $user->getEmail());
        $this->assertFalse('false' === $user->getPassword());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse('false' === $user->getName());
        $this->assertFalse([] === $user->getRoles());
        $this->assertFalse('false' === $user->getDescription());
        $this->assertFalse($user->hasAccess());
        $this->assertFalse($user->getMedias()->contains(new Media()));
        $this->assertFalse(is_int($user->getId()));
        $this->assertFalse('false' === $user->getUserIdentifier());
    }

    public function testIsEmpty(): void
    {
        $user = new User();

        $this->assertEmpty($user->getEmail());
        $this->assertEmpty($user->getPassword());
        $this->assertEmpty($user->getName());
        $this->assertEmpty($user->getDescription());
        $this->assertTrue(0 === count($user->getMedias()));
        $this->assertEmpty($user->getId());
        $this->assertEmpty($user->getUserIdentifier());
    }
}
