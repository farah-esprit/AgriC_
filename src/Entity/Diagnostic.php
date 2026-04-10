<?php

namespace App\Entity;

use App\Repository\DiagnosticRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DiagnosticRepository::class)]
#[ORM\Table(name: 'diagnostic')]
class Diagnostic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'idDiagnostic')]
    private ?int $idDiagnostic = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'dateDiagnostic')]
    #[Assert\NotBlank(message: 'La date est obligatoire.')]
    private ?string $dateDiagnostic = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'symptomes')]
    #[Assert\NotBlank(message: 'Les symptômes sont obligatoires.')]
    #[Assert\Length(min: 5, max: 500, minMessage: 'Minimum 5 caractères.', maxMessage: 'Maximum 500 caractères.')]
    private ?string $symptomes = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'informationsComplementaires')]
    #[Assert\Length(max: 1000, maxMessage: 'Maximum 1000 caractères.')]
    private ?string $informationsComplementaires = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'idCulture')]
    #[Assert\NotBlank(message: 'Veuillez sélectionner une culture.')]
    private ?int $idCulture = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'idUser')]
    private ?int $userId = null;

    public function getIdDiagnostic(): ?int { return $this->idDiagnostic; }
    public function getDateDiagnostic(): ?string { return $this->dateDiagnostic; }
    public function getSymptomes(): ?string { return $this->symptomes; }
    public function getInformationsComplementaires(): ?string { return $this->informationsComplementaires; }
    public function getIdCulture(): ?int { return $this->idCulture; }
    public function getUserId(): ?int { return $this->userId; }

    public function setIdDiagnostic(?int $idDiagnostic): self { $this->idDiagnostic = $idDiagnostic; return $this; }
    public function setDateDiagnostic(?string $dateDiagnostic): self { $this->dateDiagnostic = $dateDiagnostic; return $this; }
    public function setSymptomes(?string $symptomes): self { $this->symptomes = $symptomes; return $this; }
    public function setInformationsComplementaires(?string $informationsComplementaires): self { $this->informationsComplementaires = $informationsComplementaires; return $this; }
    public function setIdCulture(?int $idCulture): self { $this->idCulture = $idCulture; return $this; }
    public function setUserId(?int $userId): self { $this->userId = $userId; return $this; }
}
