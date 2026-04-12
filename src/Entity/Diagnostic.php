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
    #[Assert\NotBlank(message: 'La date du diagnostic est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}-\d{2}$/',
        message: 'Le format doit être YYYY-MM-DD.'
    )]
    private ?string $dateDiagnostic = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'symptomes')]
    #[Assert\NotBlank(message: 'Les symptômes sont obligatoires.')]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: 'Les symptômes doivent contenir au moins {{ limit }} caractères.',
        maxMessage: 'Les symptômes ne peuvent pas dépasser {{ limit }} caractères.'
    )]
    private ?string $symptomes = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'informationsComplementaires')]
    #[Assert\Length(
        max: 3000,
        maxMessage: 'Les informations complémentaires ne peuvent pas dépasser {{ limit }} caractères.'
    )]
    private ?string $informationsComplementaires = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'idCulture')]
    #[Assert\NotBlank(message: "L'ID de la culture est obligatoire.")]
    #[Assert\Positive(message: "L'ID de la culture doit être un entier positif.")]
    #[Assert\LessThanOrEqual(
        value: 999999,
        message: "L'ID de la culture ne peut pas dépasser {{ compared_value }}."
    )]
    private ?int $idCulture = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'idUser')]
    private ?int $userId = null;

    // ── Getters & Setters ──

    public function getIdDiagnostic(): ?int { return $this->idDiagnostic; }

    public function getDateDiagnostic(): ?string { return $this->dateDiagnostic; }
    public function setDateDiagnostic(?string $dateDiagnostic): self { $this->dateDiagnostic = $dateDiagnostic; return $this; }

    public function getSymptomes(): ?string { return $this->symptomes; }
    public function setSymptomes(?string $symptomes): self { $this->symptomes = $symptomes; return $this; }

    public function getInformationsComplementaires(): ?string { return $this->informationsComplementaires; }
    public function setInformationsComplementaires(?string $informationsComplementaires): self { $this->informationsComplementaires = $informationsComplementaires; return $this; }

    public function getIdCulture(): ?int { return $this->idCulture; }
    public function setIdCulture(?int $idCulture): self { $this->idCulture = $idCulture; return $this; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): self { $this->userId = $userId; return $this; }
}