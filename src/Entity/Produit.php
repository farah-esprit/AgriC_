<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'produit')]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idProduit = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $prix = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $categorie = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $actif = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $promo = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $tauxPromo = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Commande::class)]
    private Collection $commandes;

    #[ORM\OneToOne(mappedBy: 'produit', targetEntity: Stock::class)]
    private ?Stock $stock = null;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

    public function getIdProduit(): ?int { return $this->idProduit; }
    public function getNom(): ?string { return $this->nom; }
    public function getDescription(): ?string { return $this->description; }
    public function getPrix(): ?float { return $this->prix; }
    public function getCategorie(): ?string { return $this->categorie; }
    public function getActif(): ?bool { return $this->actif; }
    public function getImagePath(): ?string { return $this->imagePath; }
    public function getPromo(): ?bool { return $this->promo; }
    public function getTauxPromo(): ?float { return $this->tauxPromo; }
    public function getCommandes(): Collection { return $this->commandes; }
    public function getStock(): ?Stock { return $this->stock; }

    public function setIdProduit(?int $idProduit): self { $this->idProduit = $idProduit; return $this; }
    public function setNom(?string $nom): self { $this->nom = $nom; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setPrix(?float $prix): self { $this->prix = $prix; return $this; }
    public function setCategorie(?string $categorie): self { $this->categorie = $categorie; return $this; }
    public function setActif(?bool $actif): self { $this->actif = $actif; return $this; }
    public function setImagePath(?string $imagePath): self { $this->imagePath = $imagePath; return $this; }
    public function setPromo(?bool $promo): self { $this->promo = $promo; return $this; }
    public function setTauxPromo(?float $tauxPromo): self { $this->tauxPromo = $tauxPromo; return $this; }

    public function getPrixPromo(): ?float
    {
        if ($this->promo && $this->tauxPromo > 0) {
            return round($this->prix * (1 - $this->tauxPromo / 100), 2);
        }
        return $this->prix;
    }
}
