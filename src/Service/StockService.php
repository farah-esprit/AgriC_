<?php

namespace App\Service;

use App\Entity\Stock;

class StockService
{
    /**
     * Rule: An order can only be placed if the requested quantity is less than or equal to the available quantity.
     */
    public function isStockSufficient(Stock $stock, int $requestedQuantity): bool
    {
        if ($requestedQuantity <= 0) {
            return false;
        }

        return $stock->getDisponible() >= $requestedQuantity;
    }

    /**
     * Rule: When an order is confirmed, the available stock quantity must be decreased.
     */
    public function updateStock(Stock $stock, int $orderedQuantity): void
    {
        if ($orderedQuantity > 0) {
            $newDisponible = $stock->getDisponible() - $orderedQuantity;
            $stock->setDisponible(max(0, $newDisponible));
        }
    }
}
