<?php

namespace App\Service;

use App\Repository\StockRepository;
use App\Repository\ProduitRepository;

class StockChatbotService
{
    public function __construct(
        private StockRepository $stockRepository,
        private ProduitRepository $produitRepository
    ) {}

    /** @return array<string, mixed> */
    public function getReply(string $message): array
    {
        $message = mb_strtolower(trim($message));

        if (str_contains($message, 'alerte') || str_contains($message, 'faible') || str_contains($message, 'manque')) {
            return $this->getAlertsReply();
        }

        if (str_contains($message, 'liste') || str_contains($message, 'tout') || str_contains($message, 'état')) {
            return $this->getAllStockReply();
        }

        if (str_contains($message, 'aide') || str_contains($message, 'bonjour') || str_contains($message, 'salut')) {
            return $this->getHelpReply();
        }

        // Search for a specific product
        return $this->searchProductReply($message);
    }

    /** @return array<string, mixed> */
    private function getAlertsReply(): array
    {
        /** @var \App\Entity\Stock[] $stocks */
        $stocks = $this->stockRepository->findAll();
        $alerts = [];

        foreach ($stocks as $stock) {
            $produit = $stock->getProduit();
            if ($produit && $stock->getDisponible() <= $stock->getSeuilAlert()) {
                $alerts[] = sprintf("⚠️ **%s** : %d restant(s) (Seuil: %d)", 
                    $produit->getNom(), 
                    $stock->getDisponible(), 
                    $stock->getSeuilAlert()
                );
            }
        }

        if (empty($alerts)) {
            return [
                'text' => "✅ Tous les stocks sont corrects. Aucune alerte à signaler !",
                'suggestions' => ["📊 Voir tout le stock", "🆘 Aide"]
            ];
        }

        return [
            'text' => "Voici les produits en stock faible :\n\n" . implode("\n", $alerts),
            'suggestions' => ["📊 Voir tout le stock", "🆘 Aide"]
        ];
    }

    /** @return array<string, mixed> */
    private function getAllStockReply(): array
    {
        /** @var \App\Entity\Stock[] $stocks */
        $stocks = $this->stockRepository->findAll();
        $lines = [];

        foreach ($stocks as $stock) {
            $produit = $stock->getProduit();
            if (!$produit) continue;
            
            $status = $stock->getDisponible() <= $stock->getSeuilAlert() ? '⚠️' : '✅';
            $lines[] = sprintf("%s **%s** : %d en stock", $status, $produit->getNom(), $stock->getDisponible());
        }

        if (empty($lines)) {
            return [
                'text' => "Le stock est actuellement vide.",
                'suggestions' => ["🆘 Aide"]
            ];
        }

        return [
            'text' => "Voici l'état global du stock :\n\n" . implode("\n", $lines),
            'suggestions' => ["⚠️ Alertes", "🆘 Aide"]
        ];
    }

    /** @return array<string, mixed> */
    private function getHelpReply(): array
    {
        return [
            'text' => "Bonjour ! Je suis l'assistant stock d'AgriC. 🌾\n\nVous pouvez me demander :\n- **'alerte'** : produits en stock faible.\n- **'liste'** : tout le stock.\n- **'[nom du produit]'** : stock d'un produit spécifique.",
            'suggestions' => ["⚠️ Alertes", "📊 État global"]
        ];
    }

    /** @return array<string, mixed> */
    private function searchProductReply(string $query): array
    {
        /** @var \App\Entity\Produit[] $produits */
        $produits = $this->produitRepository->findAll();
        $found = null;

        foreach ($produits as $p) {
            $nom = $p->getNom() ?? '';
            if (str_contains(mb_strtolower($nom), $query)) {
                $found = $p;
                break;
            }
        }

        if ($found) {
            $stock = $found->getStock();
            if (!$stock) {
                return [
                    'text' => "Le produit **" . $found->getNom() . "** n'a pas de suivi de stock configuré.",
                    'suggestions' => ["📊 État global", "🆘 Aide"]
                ];
            }

            $status = $stock->getDisponible() <= $stock->getSeuilAlert() ? '⚠️ (Bas)' : '✅ (OK)';
            return [
                'text' => sprintf("Détails pour **%s** :\n- Disponible : %d\n- Seuil d'alerte : %d\n- Statut : %s", 
                    $found->getNom(), $stock->getDisponible(), $stock->getSeuilAlert(), $status),
                'suggestions' => ["⚠️ Alertes", "📊 État global"]
            ];
        }

        return [
            'text' => "Désolé, je ne trouve pas de produit correspondant à '" . $query . "'. Essayez 'liste' pour voir tout le stock.",
            'suggestions' => ["⚠️ Alertes", "📊 État global", "🆘 Aide"]
        ];
    }
}
