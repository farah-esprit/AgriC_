<?php

namespace App\Entity;

use App\Repository\CultureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CultureRepository::class)]
#[ORM\Table(name: 'culture')]
class Culture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'idCulture')]
    private ?int $idCulture = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'nom')]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Minimum 2 caractères.', maxMessage: 'Maximum 100 caractères.')]
    #[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\s\'-]+$/', message: 'Le nom doit contenir uniquement des lettres.')]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'type')]
    #[Assert\NotBlank(message: 'Le type est obligatoire.')]
    #[Assert\Choice(
        choices: ['Céréales', 'Légumes', 'Fruits', 'Oléagineux', 'Fourragères', 'Autre'],
        message: 'Type invalide.'
    )]
    private ?string $type = null;

    #[ORM\Column(type: 'float', nullable: true, name: 'superficie')]
    #[Assert\NotBlank(message: 'La superficie est obligatoire.')]
    #[Assert\Positive(message: 'La superficie doit être positive.')]
    private ?float $superficie = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'localisation')]
    #[Assert\NotBlank(message: 'La localisation est obligatoire.')]
    #[Assert\Length(min: 2, max: 150, minMessage: 'Minimum 2 caractères.', maxMessage: 'Maximum 150 caractères.')]
    #[Assert\Regex(pattern: '/^[a-zA-ZÀ-ÿ\s\'-]+$/', message: 'La localisation doit contenir uniquement des lettres.')]
    private ?string $localisation = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'idUser')]
    private ?int $userId = null;

    #[ORM\Column(type: 'string', nullable: true, name: 'image')]
    private ?string $image = null;

    public function getIdCulture(): ?int { return $this->idCulture; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): self { $this->nom = $nom; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): self { $this->type = $type; return $this; }

    public function getSuperficie(): ?float { return $this->superficie; }
    public function setSuperficie(?float $superficie): self { $this->superficie = $superficie; return $this; }

    public function getLocalisation(): ?string { return $this->localisation; }
    public function setLocalisation(?string $localisation): self { $this->localisation = $localisation; return $this; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): self { $this->userId = $userId; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }
}
