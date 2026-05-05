<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $user = new User();
        $nom = 'Jean Dupont';
        $email = 'jean.dupont@example.com';
        $tel = '12345678';
        $role = 'ADMIN';
        $etat = 'ACTIF';

        $user->setNom($nom)
            ->setEmail($email)
            ->setTelephone($tel)
            ->setRole($role)
            ->setEtatCompte($etat)
            ->setIsVerified(true);

        $this->assertEquals($nom, $user->getNom());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($tel, $user->getTelephone());
        $this->assertEquals($role, $user->getRole());
        $this->assertEquals($etat, $user->getEtatCompte());
        $this->assertTrue($user->isVerified());
    }

    public function testGetRoles(): void
    {
        $user = new User();
        
        // Default role
        $this->assertContains('ROLE_AGRICULTEUR', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());

        // Admin role
        $user->setRole('ADMIN');
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }
}
