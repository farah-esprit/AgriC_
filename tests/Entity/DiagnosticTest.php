<?php

namespace App\Tests\Entity;

use App\Entity\Diagnostic;
use App\Entity\Culture;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class DiagnosticTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $diagnostic = new Diagnostic();
        $date = '2024-05-04';
        $symptomes = 'Feuilles jaunies avec des taches brunes.';
        $infos = 'Plusieurs plantes affectées dans le secteur A.';
        $resultat = 'Mildiou détecté.';
        $image = 'test_image.jpg';

        $diagnostic->setDateDiagnostic($date)
            ->setSymptomes($symptomes)
            ->setInformationsComplementaires($infos)
            ->setResultat($resultat)
            ->setImage($image);

        $this->assertEquals($date, $diagnostic->getDateDiagnostic());
        $this->assertEquals($symptomes, $diagnostic->getSymptomes());
        $this->assertEquals($infos, $diagnostic->getInformationsComplementaires());
        $this->assertEquals($resultat, $diagnostic->getResultat());
        $this->assertEquals($image, $diagnostic->getImage());
    }

    public function testRelationWithCulture(): void
    {
        $diagnostic = new Diagnostic();
        $culture = new Culture();
        
        $diagnostic->setCulture($culture);
        $this->assertSame($culture, $diagnostic->getCulture());
    }

    public function testRelationWithUser(): void
    {
        $diagnostic = new Diagnostic();
        $user = new User();
        
        $diagnostic->setUser($user);
        $this->assertSame($user, $diagnostic->getUser());
    }
}
