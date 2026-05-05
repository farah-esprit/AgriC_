<?php

namespace App\Tests\Service;

use App\Entity\Evenement;
use App\Service\EventService;
use PHPUnit\Framework\TestCase;

class EventServiceTest extends TestCase
{
    private EventService $eventService;

    protected function setUp(): void
    {
        $this->eventService = new EventService();
    }

    public function testIsFull(): void
    {
        $event = new Evenement();
        $event->setCapaciteMax(100);

        $this->assertFalse($this->eventService->isFull($event, 50));
        $this->assertTrue($this->eventService->isFull($event, 100));
        $this->assertTrue($this->eventService->isFull($event, 101));
    }

    public function testIsOngoing(): void
    {
        $event = new Evenement();

        // Case 1: Ongoing
        $event->setDateDebut((new \DateTime())->modify('-1 hour'));
        $event->setDateFin((new \DateTime())->modify('+1 hour'));
        $this->assertTrue($this->eventService->isOngoing($event));

        // Case 2: Past
        $event->setDateDebut((new \DateTime())->modify('-5 hours'));
        $event->setDateFin((new \DateTime())->modify('-2 hours'));
        $this->assertFalse($this->eventService->isOngoing($event));

        // Case 3: Future
        $event->setDateDebut((new \DateTime())->modify('+2 hours'));
        $event->setDateFin((new \DateTime())->modify('+5 hours'));
        $this->assertFalse($this->eventService->isOngoing($event));
    }
}
