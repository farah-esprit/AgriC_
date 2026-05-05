<?php

namespace App\Tests\Entity;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CommandeTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $commande = new Commande();
        $date = '2024-05-04';
        $statut = 'En attente';
        $quantite = 5;
        $prixTotal = 229.95;

        $commande->setDateCommande($date)
            ->setStatut($statut)
            ->setQuantiteCommandee($quantite)
            ->setPrixTotal($prixTotal);

        $this->assertEquals($date, $commande->getDateCommande());
        $this->assertEquals($statut, $commande->getStatut());
        $this->assertEquals($quantite, $commande->getQuantiteCommandee());
        $this->assertEquals($prixTotal, $commande->getPrixTotal());
    }

    public function testRelationWithProduit(): void
    {
        $commande = new Commande();
        $produit = new Produit();
        
        $commande->setProduit($produit);
        $this->assertSame($produit, $commande->getProduit());
    }

    public function testRelationWithUser(): void
    {
        $commande = new Commande();
        $user = new User();
        
        $commande->setUser($user);
        $this->assertSame($user, $commande->getUser());
    }
}
