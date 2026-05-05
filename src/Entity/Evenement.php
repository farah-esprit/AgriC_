<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
#[ORM\Index(columns: ['date_debut'])]
#[ORM\Index(columns: ['organisateur_id'])]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idEvenement = null;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(min: 5, max: 150, minMessage: 'Le titre doit faire au moins {{ limit }} caractères.')]
    private ?string $titre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\NotBlank(message: 'La date de début est obligatoire.')]
    #[Assert\Type(type: "\DateTimeInterface")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\NotBlank(message: 'La date de fin est obligatoire.')]
    #[Assert\Type(type: "\DateTimeInterface")]
    #[Assert\GreaterThan(propertyPath: "dateDebut", message: "La date de fin doit être postérieure à la date de début.")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Le lieu est obligatoire.')]
    private ?string $lieu = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank(message: 'La capacité maximale est obligatoire.')]
    #[Assert\Positive(message: 'La capacité doit être un nombre positif.')]
    private ?int $capaciteMax = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'evenements')]
    #[ORM\JoinColumn(name: 'organisateur_id', referencedColumnName: 'user_id', nullable: true)]
    private ?User $organisateur = null;

    /** @var Collection<int, NoteEvenement> */
    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: NoteEvenement::class, orphanRemoval: true)]
    private Collection $notes;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
    }

    public function getIdEvenement(): ?int { return $this->idEvenement; }
    public function getTitre(): ?string { return $this->titre; }
    public function getDescription(): ?string { return $this->description; }
    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function getLieu(): ?string { return $this->lieu; }
    public function getCapaciteMax(): ?int { return $this->capaciteMax; }
    public function getOrganisateur(): ?User { return $this->organisateur; }

    public function setIdEvenement(?int $idEvenement): self { $this->idEvenement = $idEvenement; return $this; }
    public function setTitre(?string $titre): self { $this->titre = $titre; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setDateDebut(?\DateTimeInterface $dateDebut): self { $this->dateDebut = $dateDebut; return $this; }
    public function setDateFin(?\DateTimeInterface $dateFin): self { $this->dateFin = $dateFin; return $this; }
    public function setLieu(?string $lieu): self { $this->lieu = $lieu; return $this; }
    public function setCapaciteMax(?int $capaciteMax): self { $this->capaciteMax = $capaciteMax; return $this; }
    public function setOrganisateur(?User $organisateur): self { $this->organisateur = $organisateur; return $this; }

    /**
     * @return Collection<int, NoteEvenement>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function getAverageRating(): float
    {
        if ($this->notes->isEmpty()) {
            return 0;
        }

        $sum = 0;
        foreach ($this->notes as $note) {
            $sum += $note->getValeur();
        }

        return round($sum / $this->notes->count(), 1);
    }
}
