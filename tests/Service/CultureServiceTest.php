<?php

namespace App\Tests\Service;

use App\Entity\Culture;
use App\Entity\Diagnostic;
use App\Service\CultureService;
use PHPUnit\Framework\TestCase;

class CultureServiceTest extends TestCase
{
    private CultureService $cultureService;

    protected function setUp(): void
    {
        $this->cultureService = new CultureService();
    }

    public function testIsReadyForHarvest(): void
    {
        $culture = new Culture();
        $culture->setCycleCroissance(90);

        // Case 1: Ready (planted 100 days ago)
        $dateSemis = (new \DateTime())->modify('-100 days');
        $culture->setDateSemis($dateSemis);
        $this->assertTrue($this->cultureService->isReadyForHarvest($culture));

        // Case 2: Not ready (planted 50 days ago)
        $dateSemis = (new \DateTime())->modify('-50 days');
        $culture->setDateSemis($dateSemis);
        $this->assertFalse($this->cultureService->isReadyForHarvest($culture));

        // Case 3: Just ready (planted 90 days ago)
        $dateSemis = (new \DateTime())->modify('-90 days');
        $culture->setDateSemis($dateSemis);
        $this->assertTrue($this->cultureService->isReadyForHarvest($culture));
    }

    public function testIsDiagnosticCritical(): void
    {
        $diagnostic = new Diagnostic();

        // Critical symptoms
        $diagnostic->setSymptomes('Il y a de la pourriture sur les racines.');
        $this->assertTrue($this->cultureService->isDiagnosticCritical($diagnostic));

        $diagnostic->setSymptomes('Urgent: infestation massive de ravageurs.');
        $this->assertTrue($this->cultureService->isDiagnosticCritical($diagnostic));

        // Non-critical symptoms
        $diagnostic->setSymptomes('Quelques feuilles un peu jaunes.');
        $this->assertFalse($this->cultureService->isDiagnosticCritical($diagnostic));

        $diagnostic->setSymptomes(null);
        $this->assertFalse($this->cultureService->isDiagnosticCritical($diagnostic));
    }
}
