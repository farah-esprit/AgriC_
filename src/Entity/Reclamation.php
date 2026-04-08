<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
#[ORM\Table(name: 'reclamation')]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idReclamation = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "L'objet est obligatoire.")]
    #[Assert\Length(min: 5, max: 100, minMessage: "L'objet doit faire au moins {{ limit }} caractères.")]
    private ?string $objet = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(min: 10, minMessage: 'La description doit être plus détaillée (au moins {{ limit }} caractères).')]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $dateCreation = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $priorite = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reclamations')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: true)]
    private ?User $utilisateur = null;

    public function getIdReclamation(): ?int { return $this->idReclamation; }
    public function getObjet(): ?string { return $this->objet; }
    public function getDescription(): ?string { return $this->description; }
    public function getDateCreation(): ?string { return $this->dateCreation; }
    public function getStatut(): ?string { return $this->statut; }
    public function getPriorite(): ?string { return $this->priorite; }
    public function getType(): ?string { return $this->type; }
    public function getUtilisateur(): ?User { return $this->utilisateur; }

    public function setIdReclamation(?int $idReclamation): self { $this->idReclamation = $idReclamation; return $this; }
    public function setObjet(?string $objet): self { $this->objet = $objet; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setDateCreation(?string $dateCreation): self { $this->dateCreation = $dateCreation; return $this; }
    public function setStatut(?string $statut): self { $this->statut = $statut; return $this; }
    public function setPriorite(?string $priorite): self { $this->priorite = $priorite; return $this; }
    public function setType(?string $type): self { $this->type = $type; return $this; }
    public function setUtilisateur(?User $utilisateur): self { $this->utilisateur = $utilisateur; return $this; }
}
