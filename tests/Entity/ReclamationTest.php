<?php

namespace App\Tests\Entity;

use App\Entity\Reclamation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ReclamationTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $reclamation = new Reclamation();
        $objet = 'Problème de connexion';
        $desc = 'Je ne peux pas me connecter à mon compte depuis ce matin.';
        $date = new \DateTime();
        $statut = 'En cours';
        $priorite = 'Élevée';
        $type = 'Problème technique';

        $reclamation->setObjet($objet)
            ->setDescription($desc)
            ->setDateCreation($date)
            ->setStatut($statut)
            ->setPriorite($priorite)
            ->setType($type);

        $this->assertEquals($objet, $reclamation->getObjet());
        $this->assertEquals($desc, $reclamation->getDescription());
        $this->assertEquals($date, $reclamation->getDateCreation());
        $this->assertEquals($statut, $reclamation->getStatut());
        $this->assertEquals($priorite, $reclamation->getPriorite());
        $this->assertEquals($type, $reclamation->getType());
    }

    public function testRelationWithUser(): void
    {
        $reclamation = new Reclamation();
        $user = new User();
        
        $reclamation->setUtilisateur($user);
        $this->assertSame($user, $reclamation->getUtilisateur());
    }
}
