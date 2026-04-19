<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\AnalyticsBundle\Service\AnalyticsService;

class StockAnalyticsController extends AbstractController
{
    #[Route('/stock/dashboard', name: 'stock_dashboard')]
    public function dashboard(AnalyticsService $analyticsService)
    {
        $stats = $analyticsService->getDashboardStats();

        return $this->render('stock/dashboard.html.twig', [
            'stats' => $stats
        ]);
    }
}