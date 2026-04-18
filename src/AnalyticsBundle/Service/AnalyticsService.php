<?php

namespace App\AnalyticsBundle\Service;

use App\Repository\StockRepository;
use App\Repository\ProduitRepository;

class AnalyticsService
{
    private $stockRepository;
    private $produitRepository;
    private $predictionService;

    public function __construct(StockRepository $stockRepository, ProduitRepository $produitRepository, PredictionService $predictionService)
    {
        $this->stockRepository = $stockRepository;
        $this->produitRepository = $produitRepository;
        $this->predictionService = $predictionService;
    }

    public function getDashboardStats(): array
    {
        $stocks = $this->stockRepository->findAllWithProduit();
        
        $totalProducts = count($stocks);
        $lowStockCount = 0;
        $totalQuantity = 0;
        $totalValue = 0;
        $categoryDistribution = [];

        foreach ($stocks as $stock) {
            $product = $stock->getProduit();
            if (!$product) continue;

            $qty = $stock->getDisponible() ?? 0;
            $totalQuantity += $qty;
            
            // Check low stock
            if ($qty <= ($stock->getSeuilAlert() ?? 0)) {
                $lowStockCount++;
            }

            // AI Prediction
            $stock->predictedDemand = $this->predictionService->predictDemand($product->getIdProduit());

            // Total value
            $totalValue += $qty * ($product->getPrix() ?? 0);

            // Category distribution
            $cat = $product->getCategorie() ?? 'Autre';
            if (!isset($categoryDistribution[$cat])) {
                $categoryDistribution[$cat] = 0;
            }
            $categoryDistribution[$cat]++;
        }

        // Prepare chart data for categories
        $chartData = [
            'labels' => array_keys($categoryDistribution),
            'datasets' => [
                [
                    'data' => array_values($categoryDistribution),
                    'backgroundColor' => ['#7BA00E', '#22c55e', '#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                ]
            ]
        ];

        return [
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'totalQuantity' => $totalQuantity,
            'totalValue' => round($totalValue, 2),
            'categoryChart' => $chartData,
            'recentStocks' => array_slice($stocks, 0, 5), // last 5 products for a quick overview table
        ];
    }
}
