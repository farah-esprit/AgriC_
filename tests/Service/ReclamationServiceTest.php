<?php

namespace App\Tests\Service;

use App\Entity\Reclamation;
use App\Service\ReclamationService;
use PHPUnit\Framework\TestCase;

class ReclamationServiceTest extends TestCase
{
    private ReclamationService $reclamationService;

    protected function setUp(): void
    {
        $this->reclamationService = new ReclamationService();
    }

    public function testIsUrgent(): void
    {
        $reclamation = new Reclamation();

        // Urgent by priority
        $reclamation->setPriorite('Urgente');
        $this->assertTrue($this->reclamationService->isUrgent($reclamation));

        // Urgent by keywords
        $reclamation->setPriorite('Moyenne');
        $reclamation->setDescription('Il y a un danger de perte de récolte.');
        $this->assertTrue($this->reclamationService->isUrgent($reclamation));

        // Not urgent
        $reclamation->setDescription('Juste une simple question.');
        $this->assertFalse($this->reclamationService->isUrgent($reclamation));
    }

    public function testCanClose(): void
    {
        $reclamation = new Reclamation();

        // Cannot close if not resolved
        $reclamation->setStatut('En cours');
        $this->assertFalse($this->reclamationService->canClose($reclamation));

        // Can close if resolved
        $reclamation->setStatut('Résolu');
        $this->assertTrue($this->reclamationService->canClose($reclamation));
    }
}
