<?php

namespace App\Service;

use App\Entity\Culture;
use App\Entity\Diagnostic;

class CultureService
{
    /**
     * Rule: Determine if a culture is ready for harvest based on planting date and cycle.
     */
    public function isReadyForHarvest(Culture $culture): bool
    {
        $dateSemis = $culture->getDateSemis();
        $cycle = $culture->getCycleCroissance();

        if (!$dateSemis || $cycle === null) {
            return false;
        }

        $now = new \DateTime();
        /** @var \DateTime $dateRecolte */
        $dateRecolte = clone $dateSemis;
        $dateRecolte->modify("+$cycle days");

        return $now >= $dateRecolte;
    }

    /**
     * Rule: Identify critical diagnostics based on symptoms.
     */
    public function isDiagnosticCritical(Diagnostic $diagnostic): bool
    {
        $symptomes = mb_strtolower($diagnostic->getSymptomes() ?? '');
        
        $criticalKeywords = ['mort', 'pourriture', 'ravageur', 'grave', 'urgent', 'infestation'];

        foreach ($criticalKeywords as $keyword) {
            if (str_contains($symptomes, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
