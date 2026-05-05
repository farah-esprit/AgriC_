<?php

namespace App\Tests\Entity;

use App\Entity\Evenement;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class EvenementTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $event = new Evenement();
        $titre = 'Salon de l\'Agriculture';
        $desc = 'Un grand salon pour les agriculteurs.';
        $debut = new \DateTime('2024-06-01 09:00:00');
        $fin = new \DateTime('2024-06-05 18:00:00');
        $lieu = 'Paris Expo';
        $capacite = 500;

        $event->setTitre($titre)
            ->setDescription($desc)
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->setLieu($lieu)
            ->setCapaciteMax($capacite);

        $this->assertEquals($titre, $event->getTitre());
        $this->assertEquals($desc, $event->getDescription());
        $this->assertEquals($debut, $event->getDateDebut());
        $this->assertEquals($fin, $event->getDateFin());
        $this->assertEquals($lieu, $event->getLieu());
        $this->assertEquals($capacite, $event->getCapaciteMax());
    }

    public function testRelationWithOrganisateur(): void
    {
        $event = new Evenement();
        $user = new User();
        
        $event->setOrganisateur($user);
        $this->assertSame($user, $event->getOrganisateur());
    }

    public function testAverageRatingEmpty(): void
    {
        $event = new Evenement();
        $this->assertEquals(0, $event->getAverageRating());
    }
}
