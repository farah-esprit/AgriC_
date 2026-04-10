<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idEvenement = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(min: 5, max: 100, minMessage: 'Le titre doit faire au moins {{ limit }} caractères.')]
    private ?string $titre = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'La date de début est obligatoire.')]
    private ?string $dateDebut = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $dateFin = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank(message: 'La capacité maximale est obligatoire.')]
    #[Assert\Positive(message: 'La capacité doit être un nombre positif.')]
    private ?int $capaciteMax = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'evenements')]
    #[ORM\JoinColumn(name: 'organisateur_id', referencedColumnName: 'user_id', nullable: true)]
    private ?User $organisateur = null;

    public function getIdEvenement(): ?int { return $this->idEvenement; }
    public function getTitre(): ?string { return $this->titre; }
    public function getDescription(): ?string { return $this->description; }
    public function getDateDebut(): ?string { return $this->dateDebut; }
    public function getDateFin(): ?string { return $this->dateFin; }
    public function getLieu(): ?string { return $this->lieu; }
    public function getCapaciteMax(): ?int { return $this->capaciteMax; }
    public function getOrganisateur(): ?User { return $this->organisateur; }

    public function setIdEvenement(?int $idEvenement): self { $this->idEvenement = $idEvenement; return $this; }
    public function setTitre(?string $titre): self { $this->titre = $titre; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setDateDebut(?string $dateDebut): self { $this->dateDebut = $dateDebut; return $this; }
    public function setDateFin(?string $dateFin): self { $this->dateFin = $dateFin; return $this; }
    public function setLieu(?string $lieu): self { $this->lieu = $lieu; return $this; }
    public function setCapaciteMax(?int $capaciteMax): self { $this->capaciteMax = $capaciteMax; return $this; }
    public function setOrganisateur(?User $organisateur): self { $this->organisateur = $organisateur; return $this; }
}
