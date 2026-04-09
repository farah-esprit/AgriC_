<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idCommande = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'La date de commande est obligatoire.')]
    private ?string $dateCommande = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    private ?string $statut = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank(message: 'La quantité est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'La quantité doit être positive ou nulle.')]
    private ?int $quantiteCommandee = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Assert\NotBlank(message: 'Le prix total est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix total doit être positif ou nul.')]
    private ?float $prixTotal = null;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id_produit')]
    #[Assert\NotBlank(message: 'Le produit est obligatoire.')]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id')]
    #[Assert\NotBlank(message: "L'utilisateur est obligatoire.")]
    private ?User $user = null;

    public function getIdCommande(): ?int { return $this->idCommande; }
    public function getDateCommande(): ?string { return $this->dateCommande; }
    public function getStatut(): ?string { return $this->statut; }
    public function getQuantiteCommandee(): ?int { return $this->quantiteCommandee; }
    public function getPrixTotal(): ?float { return $this->prixTotal; }
    public function getProduit(): ?Produit { return $this->produit; }
    public function getUser(): ?User { return $this->user; }

    public function setIdCommande(?int $idCommande): self { $this->idCommande = $idCommande; return $this; }
    public function setDateCommande(?string $dateCommande): self { $this->dateCommande = $dateCommande; return $this; }
    public function setStatut(?string $statut): self { $this->statut = $statut; return $this; }
    public function setQuantiteCommandee(?int $quantiteCommandee): self { $this->quantiteCommandee = $quantiteCommandee; return $this; }
    public function setPrixTotal(?float $prixTotal): self { $this->prixTotal = $prixTotal; return $this; }
    public function setProduit(?Produit $produit): self { $this->produit = $produit; return $this; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
}
