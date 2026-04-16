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

        $this->assertTrue($user->getEmail() === 'test@test.com');
        $this->assertTrue($user->getPassword() === 'test');
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->getName() === 'test');
        $this->assertTrue($user->getRoles() === ['ROLE_ADMIN', 'ROLE_USER']);
        $this->assertTrue($user->getDescription() === 'test');
        $this->assertTrue($user->hasAccess());
        $this->assertTrue($user->getMedias()->contains($media));
        $this->assertTrue($user->getId() === null);
        $this->assertTrue($user->getUserIdentifier() === 'test@test.com');
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

        $this->assertFalse($user->getEmail() === 'false');
        $this->assertFalse($user->getPassword() === 'false');
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->getName() === 'false');
        $this->assertFalse($user->getRoles() === []);
        $this->assertFalse($user->getDescription() === 'false');
        $this->assertFalse($user->hasAccess());
        $this->assertFalse($user->getMedias()->contains(new Media()));
        $this->assertFalse($user->getId() === !null);
        $this->assertFalse($user->getUserIdentifier() === 'false');
    }

    public function testIsEmpty(): void
    {
        $user = new User();

        $this->assertEmpty($user->getEmail());
        $this->assertEmpty($user->getPassword());
        $this->assertEmpty($user->getName());
        $this->assertEmpty($user->getDescription());
        $this->assertEmpty($user->getMedias());
        $this->assertEmpty($user->getId());
        $this->assertEmpty($user->getUserIdentifier());
    }
}
