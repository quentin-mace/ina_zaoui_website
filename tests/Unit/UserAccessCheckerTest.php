<?php

namespace App\Tests\Unit;

use App\Entity\User;
use App\Security\UserAccessChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserAccessCheckerTest extends TestCase
{
    public function testPreAuthForUserWithNoAccess(): void
    {
        $user = new User();
        $checker = new UserAccessChecker();

        $user->setHasAccess(false);
        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Ce compte a été désactivé. Vous n\'avez plus accès.');
        $checker->checkPreAuth($user);
    }

    public function testPreAuthForUserWithAccess(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $user = new User();
            $checker = new UserAccessChecker();

            $user->setHasAccess(true);
            $checker->checkPreAuth($user);
        } catch (\Exception $e) {
            $this->fail('No exception expected. User should have access');
        }
    }

    public function testWithNonUserUserInterface(): void
    {
        $this->expectNotToPerformAssertions();

        $user = $this->getMockBuilder(UserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $checker = new UserAccessChecker();

        $checker->checkPreAuth($user);
        $checker->checkPostAuth($user);
    }
}
