<?php

namespace App\Tests\Entity;

use App\Entity\Culture;
use App\Entity\Diagnostic;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CultureTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $culture = new Culture();
        $nom = 'Blé Dur';
        $type = 'Céréales';
        $superficie = 15.5;
        $localisation = 'Parcelle Nord';
        $image = 'ble.jpg';
        $dateSemis = new \DateTime('2024-01-15');
        $cycle = 120;

        $culture->setNom($nom)
            ->setType($type)
            ->setSuperficie($superficie)
            ->setLocalisation($localisation)
            ->setImage($image)
            ->setDateSemis($dateSemis)
            ->setCycleCroissance($cycle);

        $this->assertEquals($nom, $culture->getNom());
        $this->assertEquals($type, $culture->getType());
        $this->assertEquals($superficie, $culture->getSuperficie());
        $this->assertEquals($localisation, $culture->getLocalisation());
        $this->assertEquals($image, $culture->getImage());
        $this->assertEquals($dateSemis, $culture->getDateSemis());
        $this->assertEquals($cycle, $culture->getCycleCroissance());
    }

    public function testAddRemoveDiagnostic(): void
    {
        $culture = new Culture();
        $diagnostic = new Diagnostic();

        $culture->addDiagnostic($diagnostic);
        $this->assertCount(1, $culture->getDiagnostics());
        $this->assertContains($diagnostic, $culture->getDiagnostics());
        $this->assertSame($culture, $diagnostic->getCulture());

        $culture->removeDiagnostic($diagnostic);
        $this->assertCount(0, $culture->getDiagnostics());
        $this->assertNull($diagnostic->getCulture());
    }

    public function testRelationWithUser(): void
    {
        $culture = new Culture();
        $user = new User();
        
        $culture->setUser($user);
        $this->assertSame($user, $culture->getUser());
    }
}
