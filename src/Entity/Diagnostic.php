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

    // ── Relation ManyToOne vers Culture ──
    #[ORM\ManyToOne(targetEntity: Culture::class, inversedBy: 'diagnostics')]
    #[ORM\JoinColumn(name: 'idCulture', referencedColumnName: 'idCulture', nullable: true)]
    #[Assert\NotNull(message: "La culture est obligatoire.")]
    private ?Culture $culture = null;

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

    public function getCulture(): ?Culture { return $this->culture; }
    public function setCulture(?Culture $culture): self { $this->culture = $culture; return $this; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): self { $this->userId = $userId; return $this; }
}