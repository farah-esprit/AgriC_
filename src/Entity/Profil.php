<?php

namespace App\Entity;

use App\Repository\ProfilRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfilRepository::class)]
#[ORM\Table(name: 'profil')]
class Profil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $image = null;

    #[ORM\OneToOne(inversedBy: 'profile', targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function getId(): ?int { return $this->id; }
    public function getBio(): ?string { return $this->bio; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getImage(): ?string { return $this->image; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }

    public function setBio(?string $bio): self { $this->bio = $bio; return $this; }
    public function setTelephone(?string $telephone): self { $this->telephone = $telephone; return $this; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }
}
