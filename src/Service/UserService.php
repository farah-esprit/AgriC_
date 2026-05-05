<?php

namespace App\Service;

use App\Entity\User;

class UserService
{
    /**
     * Rule: Verify if a user has administrative rights.
     */
    public function isAdmin(User $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles());
    }

    /**
     * Rule: A user can login if their account is ACTIF and email is verified.
     */
    public function canLogin(User $user): bool
    {
        return $user->getEtatCompte() === 'ACTIF' && $user->isVerified();
    }
}
