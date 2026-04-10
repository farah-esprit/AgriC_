<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockRepository::class)]
#[ORM\Table(name: 'stock')]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id_stock')]
    private ?int $idStock = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank(message: 'La quantité est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'La quantité doit être positive ou nulle.')]
    private ?int $quantite = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank(message: 'La quantité disponible est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'La quantité disponible doit être positive ou nulle.')]
    private ?int $disponible = null;

    #[ORM\Column(type: 'integer', name: 'seuil_alert', nullable: true)]
    #[Assert\NotBlank(message: "Le seuil d'alerte est obligatoire.")]
    #[Assert\PositiveOrZero(message: "Le seuil d'alerte doit être positif ou nul.")]
    private ?int $seuilAlert = null;

    #[ORM\OneToOne(inversedBy: 'stock', targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id_produit')]
    #[Assert\NotBlank(message: 'Le produit est obligatoire.')]
    private ?Produit $produit = null;

    public function getIdStock(): ?int { return $this->idStock; }
    public function getQuantite(): ?int { return $this->quantite; }
    public function getDisponible(): ?int { return $this->disponible; }
    public function getSeuilAlert(): ?int { return $this->seuilAlert; }
    public function getProduit(): ?Produit { return $this->produit; }

    public function setIdStock(?int $idStock): self { $this->idStock = $idStock; return $this; }
    public function setQuantite(?int $quantite): self { $this->quantite = $quantite; return $this; }
    public function setDisponible(?int $disponible): self { $this->disponible = $disponible; return $this; }
    public function setSeuilAlert(?int $seuilAlert): self { $this->seuilAlert = $seuilAlert; return $this; }
    public function setProduit(?Produit $produit): self { $this->produit = $produit; return $this; }
}
