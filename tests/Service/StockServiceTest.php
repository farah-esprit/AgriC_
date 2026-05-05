<?php

namespace App\Tests\Service;

use App\Entity\Stock;
use App\Service\StockService;
use PHPUnit\Framework\TestCase;

class StockServiceTest extends TestCase
{
    private StockService $stockService;

    protected function setUp(): void
    {
        $this->stockService = new StockService();
    }

    public function testIsStockSufficient(): void
    {
        $stock = new Stock();
        $stock->setDisponible(10);

        $this->assertTrue($this->stockService->isStockSufficient($stock, 5));
        $this->assertTrue($this->stockService->isStockSufficient($stock, 10));
        $this->assertFalse($this->stockService->isStockSufficient($stock, 11));
        $this->assertFalse($this->stockService->isStockSufficient($stock, 0));
        $this->assertFalse($this->stockService->isStockSufficient($stock, -5));
    }

    public function testUpdateStock(): void
    {
        $stock = new Stock();
        $stock->setDisponible(100);

        $this->stockService->updateStock($stock, 20);
        $this->assertEquals(80, $stock->getDisponible());

        $this->stockService->updateStock($stock, 90);
        $this->assertEquals(0, $stock->getDisponible()); // Should not go below 0

        $stock->setDisponible(50);
        $this->stockService->updateStock($stock, 0);
        $this->assertEquals(50, $stock->getDisponible());
    }
}
