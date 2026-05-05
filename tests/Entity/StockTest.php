<?php

namespace App\Tests\Entity;

use App\Entity\Stock;
use App\Entity\Produit;
use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $stock = new Stock();
        $quantite = 100;
        $disponible = 80;
        $seuil = 10;

        $stock->setQuantite($quantite)
            ->setDisponible($disponible)
            ->setSeuilAlert($seuil);

        $this->assertEquals($quantite, $stock->getQuantite());
        $this->assertEquals($disponible, $stock->getDisponible());
        $this->assertEquals($seuil, $stock->getSeuilAlert());
    }

    public function testRelationWithProduit(): void
    {
        $stock = new Stock();
        $produit = new Produit();
        
        $stock->setProduit($produit);
        $this->assertSame($produit, $stock->getProduit());
    }
}
