<?php

namespace App\Tests\Entity;

use App\Entity\Produit;
use App\Entity\Commande;
use App\Entity\Stock;
use PHPUnit\Framework\TestCase;

class ProduitTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $produit = new Produit();
        $nom = 'Engrais Organique';
        $desc = 'Engrais riche en azote pour cultures maraîchères.';
        $prix = 45.99;
        $cat = 'Fertilisants';
        $image = 'engrais.jpg';

        $produit->setNom($nom)
            ->setDescription($desc)
            ->setPrix($prix)
            ->setCategorie($cat)
            ->setImagePath($image)
            ->setActif(true)
            ->setPromo(false);

        $this->assertEquals($nom, $produit->getNom());
        $this->assertEquals($desc, $produit->getDescription());
        $this->assertEquals($prix, $produit->getPrix());
        $this->assertEquals($cat, $produit->getCategorie());
        $this->assertEquals($image, $produit->getImagePath());
        $this->assertTrue($produit->isActif());
        $this->assertFalse($produit->isPromo());
    }

    public function testPrixPromoLogic(): void
    {
        $produit = new Produit();
        $produit->setPrix(100.0);
        
        // Sans promo
        $produit->setPromo(false);
        $this->assertEquals(100.0, $produit->getPrixPromo());

        // Avec promo de 20%
        $produit->setPromo(true);
        $produit->setTauxPromo(20.0);
        $this->assertEquals(80.0, $produit->getPrixPromo());

        // Avec promo de 50%
        $produit->setTauxPromo(50.0);
        $this->assertEquals(50.0, $produit->getPrixPromo());
    }

    public function testAddRemoveCommande(): void
    {
        $produit = new Produit();
        $commande = new Commande();

        $produit->addCommande($commande);
        $this->assertCount(1, $produit->getCommandes());
        $this->assertSame($produit, $commande->getProduit());

        $produit->removeCommande($commande);
        $this->assertCount(0, $produit->getCommandes());
        $this->assertNull($commande->getProduit());
    }

    public function testRelationWithStock(): void
    {
        $produit = new Produit();
        $stock = new Stock();
        
        $produit->setStock($stock);
        $this->assertSame($stock, $produit->getStock());
        $this->assertSame($produit, $stock->getProduit());
    }
}
