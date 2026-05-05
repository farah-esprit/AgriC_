<?php

namespace App\Entity;

use App\Repository\CultureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CultureRepository::class)]
#[ORM\Table(name: 'culture')]
#[ORM\Index(columns: ['idUser'])]
class Culture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'idCulture')]
    private ?int $idCulture = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'nom')]
    #[Assert\NotBlank(message: 'Le nom de la culture est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[\p{L}\s\-\.]+$/u',
        message: 'Le nom ne peut contenir que des lettres, espaces, tirets ou points.'
    )]
    #[Assert\Regex(
        pattern: '/[\p{L}]{3,}/u',
        message: 'Le nom doit contenir au moins 3 lettres consécutives.'
    )]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'type')]
    #[Assert\NotBlank(message: 'Veuillez sélectionner un type de culture.')]
    #[Assert\Choice(
        choices: ['Céréales', 'Légumes', 'Fruits', 'Oléagineux', 'Fourragères', 'Autre'],
        message: 'Le type sélectionné est invalide.'
    )]
    private ?string $type = null;

    #[ORM\Column(type: 'float', nullable: true, name: 'superficie')]
    #[Assert\NotBlank(message: 'La superficie est obligatoire.')]
    #[Assert\Positive(message: 'La superficie doit être un nombre positif.')]
    #[Assert\LessThanOrEqual(
        value: 100000,
        message: 'La superficie ne peut pas dépasser {{ compared_value }} hectares.'
    )]
    private ?float $superficie = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'localisation')]
    #[Assert\NotBlank(message: 'La localisation est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 150,
        minMessage: 'La localisation doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'La localisation ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[\p{L}\s\-\,\.]+$/u',
        message: 'La localisation ne peut contenir que des lettres, espaces, virgules, tirets ou points.'
    )]
    #[Assert\Regex(
        pattern: '/[\p{L}]{3,}/u',
        message: 'La localisation doit contenir au moins 3 lettres consécutives.'
    )]
    private ?string $localisation = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'image')]
    private ?string $image = null;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'dateSemis')]
    #[Assert\Type(type: '\DateTimeInterface', message: 'La date de semis doit être une date valide.')]
    private ?\DateTimeInterface $dateSemis = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'cycleCroissance')]
    #[Assert\PositiveOrZero(message: 'Le cycle de croissance doit être un nombre positif.')]
    private ?int $cycleCroissance = null;

    // ── Relation ManyToOne vers User ──
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'idUser', referencedColumnName: 'user_id', nullable: true)]
    private ?User $user = null;

    // ── Relation OneToMany vers Diagnostic ──
    /**
     * @var Collection<int, Diagnostic>
     */
    #[ORM\OneToMany(mappedBy: 'culture', targetEntity: Diagnostic::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $diagnostics;

    public function __construct()
    {
        $this->diagnostics = new ArrayCollection();
    }

    // ── Getters & Setters ──

    public function getIdCulture(): ?int
    {
        return $this->idCulture;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getSuperficie(): ?float
    {
        return $this->superficie;
    }

    public function setSuperficie(?float $superficie): self
    {
        $this->superficie = $superficie;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): self
    {
        $this->localisation = $localisation;
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

    public function getDateSemis(): ?\DateTimeInterface
    {
        return $this->dateSemis;
    }

    public function setDateSemis(?\DateTimeInterface $dateSemis): self
    {
        $this->dateSemis = $dateSemis;
        return $this;
    }

    public function getCycleCroissance(): ?int
    {
        return $this->cycleCroissance;
    }

    public function setCycleCroissance(?int $cycleCroissance): self
    {
        $this->cycleCroissance = $cycleCroissance;
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

    /** @return Collection<int, Diagnostic> */
    public function getDiagnostics(): Collection
    {
        return $this->diagnostics;
    }

    public function addDiagnostic(Diagnostic $diagnostic): self
    {
        if (!$this->diagnostics->contains($diagnostic)) {
            $this->diagnostics->add($diagnostic);
            $diagnostic->setCulture($this);
        }
        return $this;
    }

    public function removeDiagnostic(Diagnostic $diagnostic): self
    {
        if ($this->diagnostics->removeElement($diagnostic)) {
            if ($diagnostic->getCulture() === $this) {
                $diagnostic->setCulture(null);
            }
        }
        return $this;
    }
}