<?php

namespace App\Entity;

use App\Repository\NoteEvenementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NoteEvenementRepository::class)]
#[ORM\Table(name: 'note_evenement')]
class NoteEvenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: "La note doit être entre 1 et 5.")]
    private ?int $valeur = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(name: 'evenement_id', referencedColumnName: 'id_evenement', nullable: false, onDelete: 'CASCADE')]
    private ?Evenement $evenement = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private ?User $utilisateur = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getValeur(): ?int { return $this->valeur; }
    public function setValeur(int $valeur): self { $this->valeur = $valeur; return $this; }

    public function getEvenement(): ?Evenement { return $this->evenement; }
    public function setEvenement(?Evenement $evenement): self { $this->evenement = $evenement; return $this; }

    public function getUtilisateur(): ?User { return $this->utilisateur; }
    public function setUtilisateur(?User $utilisateur): self { $this->utilisateur = $utilisateur; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
}
