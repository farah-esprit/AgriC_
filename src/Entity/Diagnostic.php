<?php

namespace App\Entity;

use App\Repository\DiagnosticRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DiagnosticRepository::class)]
#[ORM\Table(name: 'diagnostic')]
#[ORM\Index(columns: ['idCulture'])]
#[ORM\Index(columns: ['idUser'])]
class Diagnostic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'idDiagnostic')]
    private ?int $idDiagnostic = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

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

    #[ORM\Column(type: 'text', nullable: true, name: 'resultat')]
    private ?string $resultat = null;

    // ── Relation ManyToOne vers Culture ──
    #[ORM\ManyToOne(targetEntity: Culture::class, inversedBy: 'diagnostics')]
    #[ORM\JoinColumn(name: 'idCulture', referencedColumnName: 'idCulture', nullable: true)]
    #[Assert\NotNull(message: "La culture est obligatoire.")]
    private ?Culture $culture = null;

    // ── Relation ManyToOne vers User ──
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'idUser', referencedColumnName: 'user_id', nullable: true)]
    private ?User $user = null;

    // ── Getters & Setters ──

    public function getIdDiagnostic(): ?int
    {
        return $this->idDiagnostic;
    }

    public function getDateDiagnostic(): ?string
    {
        return $this->dateDiagnostic;
    }

    public function setDateDiagnostic(?string $dateDiagnostic): self
    {
        $this->dateDiagnostic = $dateDiagnostic;
        return $this;
    }

    public function getSymptomes(): ?string
    {
        return $this->symptomes;
    }

    public function setSymptomes(?string $symptomes): self
    {
        $this->symptomes = $symptomes;
        return $this;
    }

    public function getInformationsComplementaires(): ?string
    {
        return $this->informationsComplementaires;
    }

    public function setInformationsComplementaires(?string $informationsComplementaires): self
    {
        $this->informationsComplementaires = $informationsComplementaires;
        return $this;
    }

    public function getCulture(): ?Culture
    {
        return $this->culture;
    }

    public function setCulture(?Culture $culture): self
    {
        $this->culture = $culture;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getResultat(): ?string
    {
        return $this->resultat;
    }

    public function setResultat(?string $resultat): self
    {
        $this->resultat = $resultat;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }
}