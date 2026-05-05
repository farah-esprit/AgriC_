<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userService = new UserService();
    }

    public function testIsAdmin(): void
    {
        $user = new User();

        // Not admin
        $user->setRole('AGRICULTEUR');
        $this->assertFalse($this->userService->isAdmin($user));

        // Admin
        $user->setRole('ADMIN');
        $this->assertTrue($this->userService->isAdmin($user));
    }

    public function testCanLogin(): void
    {
        $user = new User();

        // Inactive and not verified
        $user->setEtatCompte('INACTIF');
        $user->setIsVerified(false);
        $this->assertFalse($this->userService->canLogin($user));

        // Active but not verified
        $user->setEtatCompte('ACTIF');
        $this->assertFalse($this->userService->canLogin($user));

        // Active and verified
        $user->setIsVerified(true);
        $this->assertTrue($this->userService->canLogin($user));
    }
}
