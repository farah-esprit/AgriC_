<?php

namespace App\AnalyticsBundle\Service;

use App\Repository\CommandeRepository;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Psr\Log\LoggerInterface;

class PredictionService
{
    private $commandeRepository;
    private $projectDir;
    private $logger;

    public function __construct(CommandeRepository $commandeRepository, string $projectDir, LoggerInterface $logger)
    {
        $this->commandeRepository = $commandeRepository;
        $this->projectDir = $projectDir;
        $this->logger = $logger;
    }

    /**
     * Predicts demand for a given product for the next 30 days.
     */
    public function predictDemand(int $productId): float
    {
        // 1. Fetch history from DB
        $commandes = $this->commandeRepository->createQueryBuilder('c')
            ->andWhere('c.produit = :id')
            ->setParameter('id', $productId)
            ->andWhere('c.statut != :status')
            ->setParameter('status', 'ANNULEE')
            ->orderBy('c.dateCommande', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($commandes)) {
            return 0.0;
        }

        // 2. Prepare data for Python
        $data = [];
        foreach ($commandes as $commande) {
            $data[] = [
                'date' => $commande->getDateCommande(), // Expected format YYYY-MM-DD or similar
                'quantity' => $commande->getQuantiteCommandee()
            ];
        }

        // 3. Call Python script
        $scriptPath = $this->projectDir . '/scripts/predict_stock.py';
        
        // On Windows, might need 'python' or 'py'
        $process = new Process(['python', $scriptPath]);
        $process->setInput(json_encode($data));
        
        try {
            $process->run();

            if (!$process->isSuccessful()) {
                $this->logger->error('Python Prediction Failed: ' . $process->getErrorOutput());
                return $this->calculateHeuristic($data); // Fallback
            }

            $output = json_decode($process->getOutput(), true);
            
            if (isset($output['error'])) {
                $this->logger->error('Python ML Error: ' . $output['error']);
                return $this->calculateHeuristic($data);
            }

            return (float) ($output['result'] ?? 0);

        } catch (\Exception $e) {
            $this->logger->error('Prediction Process Error: ' . $e->getMessage());
            return $this->calculateHeuristic($data);
        }
    }

    /**
     * Simple fallback if Python/ML fails: Average consumption * 30 days
     */
    private function calculateHeuristic(array $data): float
    {
        if (empty($data)) return 0;
        
        $total = 0;
        foreach ($data as $item) {
            $total += $item['quantity'];
        }
        
        // Simple average (very basic)
        return round(($total / count($data)) * 5, 1); 
    }
}
