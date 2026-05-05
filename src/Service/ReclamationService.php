<?php

namespace App\Service;

use App\Entity\Reclamation;

class ReclamationService
{
    /**
     * Rule: Identify urgent reclamations based on priority or description keywords.
     */
    public function isUrgent(Reclamation $reclamation): bool
    {
        if ($reclamation->getPriorite() === 'Urgente') {
            return true;
        }

        $desc = mb_strtolower($reclamation->getDescription() ?? '');
        $urgentKeywords = ['danger', 'critique', 'perte', 'panne', 'bloqué'];

        foreach ($urgentKeywords as $keyword) {
            if (str_contains($desc, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rule: A reclamation can only be marked as "Fermé" if it was "Résolu".
     */
    public function canClose(Reclamation $reclamation): bool
    {
        return $reclamation->getStatut() === 'Résolu';
    }
}
