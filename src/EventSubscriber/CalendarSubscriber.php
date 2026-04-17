<?php

namespace App\EventSubscriber;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use App\Repository\CultureRepository;
use App\Service\CalendarAiService;
use CalendarBundle\Entity\Event;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Event\CalendarEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ActivityRepository $activityRepository,
        private readonly CultureRepository $cultureRepository,
        private readonly CalendarAiService $aiService,
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $router
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendarEvent): void
    {
        $start = $calendarEvent->getStart();
        $end = $calendarEvent->getEnd();
        $filters = $calendarEvent->getFilters();

        // 1. Toujours synchroniser/générer les suggestions IA au moment de l'affichage
        // (En conditions réelles, on pourrait faire ça via un cron job, mais ici c'est plus interactif)
        $this->syncAiSuggestions();

        // 2. Récupérer toutes les activités (Confirmées et Suggestions)
        $activities = $this->activityRepository->createQueryBuilder('a')
            ->where('a.beginAt BETWEEN :start AND :end')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();

        foreach ($activities as $activity) {
            $title = $activity->getTitle();
            if ($activity->getCulture()) {
                $title .= " (" . $activity->getCulture()->getNom() . ")";
            }

            $event = new Event(
                $title,
                $activity->getBeginAt(),
                $activity->getEndAt()
            );

            // Style personnalisé selon le type et le statut (IA ou Réel)
            $options = $this->getEventOptions($activity);
            foreach ($options as $key => $value) {
                $event->addOption($key, $value);
            }

            $calendarEvent->addEvent($event);
        }
    }

    private function syncAiSuggestions(): void
    {
        $cultures = $this->cultureRepository->findAll();
        foreach ($cultures as $culture) {
            $suggestions = $this->aiService->generateSuggestions($culture);
            foreach ($suggestions as $suggestion) {
                $this->em->persist($suggestion);
            }
        }
        $this->em->flush();
    }

    private function getEventOptions(Activity $activity): array
    {
        $type = $activity->getType();
        $confirmed = $activity->isConfirmed();

        // Couleurs de base
        $colors = [
            'Plantation' => '#4caf50', // Vert
            'Irrigation' => '#2196f3', // Bleu
            'Récolte'   => '#ffc107', // Jaune/Or
            'Traitement' => '#9c27b0', // Violet
            'Autre'      => '#607d8b'  // Gris
        ];

        $bgColor = $colors[$type] ?? $colors['Autre'];
        
        // Si c'est une suggestion IA, on rend la couleur plus claire/transparente
        // et on ajoute une bordure pointillée via CSS class
        if (!$confirmed) {
            // Transformation de la couleur en version plus claire (simulation simple)
            $bgColor = $this->lightenColor($bgColor);
        }

        return [
            'backgroundColor' => $bgColor,
            'borderColor'     => $confirmed ? $bgColor : '#333',
            'textColor'       => $type === 'Récolte' ? '#000' : '#fff',
            'className'       => !$confirmed ? 'ai-suggestion-event' : 'confirmed-event',
            'url'             => $this->router->generate('app_calendrier_show', ['id' => $activity->getId()]),
        ];
    }

    private function lightenColor($hex): string
    {
        // Version simplifiée : on retourne une couleur pastel fixe pour les tests
        // Ou on pourrait manipuler le HEX
        return $hex . '88'; // Ajout d'opacité hex
    }
}
