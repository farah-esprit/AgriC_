<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Culture;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;

class CalendarAiService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WeatherService $weatherService
    ) {}

    /**
     * Génère des suggestions pour une culture donnée si elles n'existent pas déjà.
     */
    public function generateSuggestions(Culture $culture): array
    {
        $suggestions = [];

        // 1. Suggestion d'irrigation basée sur la météo
        $irrigationSug = $this->suggestIrrigation($culture);
        if ($irrigationSug) $suggestions[] = $irrigationSug;

        // 2. Suggestion de récolte basée sur le cycle
        $recolteSug = $this->suggestHarvest($culture);
        if ($recolteSug) $suggestions[] = $recolteSug;

        return $suggestions;
    }

    private function suggestIrrigation(Culture $culture): ?Activity
    {
        $location = $culture->getLocalisation() ?? 'Tunis';
        $forecast = $this->weatherService->getForecast($location);

        if (!$forecast) return null;

        foreach ($forecast['list'] as $entry) {
            $temp = $entry['main']['temp'] ?? 0;
            $weather = $entry['weather'][0]['main'] ?? '';
            $dateStr = $entry['dt_txt'];
            $date = new \DateTime($dateStr);

            // Si température élevée (>27) et pas de pluie prévue dans les prochaines 48h
            // On prend la première occurrence pertinente
            if ($temp > 27 && $weather !== 'Rain' && $date > new \DateTime()) {
                
                // Vérifier si une suggestion d'irrigation existe déjà pour ce jour
                if ($this->hasRecentActivity($culture, 'Irrigation', $date)) {
                    continue;
                }

                $activity = new Activity();
                $activity->setTitle("Irrigation recommandée (IA)");
                $activity->setDescription("Prévision météo : {$temp}°C et ciel {$weather}. Un arrosage est conseillé pour le bien-être de vos " . $culture->getNom());
                $activity->setType('Irrigation');
                $activity->setBeginAt($date);
                $activity->setEndAt((clone $date)->modify('+1 hour'));
                $activity->setCulture($culture);
                $activity->setIsConfirmed(false);

                return $activity;
            }
        }

        return null;
    }

    private function suggestHarvest(Culture $culture): ?Activity
    {
        $dateSemis = $culture->getDateSemis();
        $cycle = $culture->getCycleCroissance();

        if (!$dateSemis || !$cycle) return null;

        $dateRecolte = (clone $dateSemis)->modify("+$cycle days");
        
        // On ne suggère que si la date est dans le futur et proche (ou si on n'a pas encore suggéré)
        if ($dateRecolte < new \DateTime()) return null;

        if ($this->hasRecentActivity($culture, 'Récolte', $dateRecolte)) {
            return null;
        }

        $activity = new Activity();
        $activity->setTitle("Récolte prévue (IA)");
        $activity->setDescription("Selon le cycle de croissance estimé ({$cycle} jours), vos " . $culture->getNom() . " devraient être prêts.");
        $activity->setType('Récolte');
        $activity->setBeginAt($dateRecolte);
        $activity->setEndAt((clone $dateRecolte)->modify('+4 hours'));
        $activity->setCulture($culture);
        $activity->setIsConfirmed(false);

        return $activity;
    }

    private function hasRecentActivity(Culture $culture, string $type, \DateTimeInterface $date): bool
    {
        $repo = $this->em->getRepository(Activity::class);
        $dayStart = (clone $date)->setTime(0, 0, 0);
        $dayEnd = (clone $date)->setTime(23, 59, 59);

        $existing = $repo->createQueryBuilder('a')
            ->where('a.culture = :culture')
            ->andWhere('a.type = :type')
            ->andWhere('a.beginAt BETWEEN :start AND :end')
            ->setParameter('culture', $culture)
            ->setParameter('type', $type)
            ->setParameter('start', $dayStart)
            ->setParameter('end', $dayEnd)
            ->getQuery()
            ->getResult();

        return count($existing) > 0;
    }
}
