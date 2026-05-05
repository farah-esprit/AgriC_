<?php

namespace App\Service;

use App\Entity\Evenement;

class EventService
{
    /**
     * Rule: Determine if an event has reached its maximum capacity.
     */
    public function isFull(Evenement $event, int $attendeeCount): bool
    {
        return $attendeeCount >= $event->getCapaciteMax();
    }

    /**
     * Rule: Check if an event is currently taking place.
     */
    public function isOngoing(Evenement $event): bool
    {
        $now = new \DateTime();
        $start = $event->getDateDebut();
        $end = $event->getDateFin();

        if (!$start || !$end) {
            return false;
        }

        return $now >= $start && $now <= $end;
    }
}
