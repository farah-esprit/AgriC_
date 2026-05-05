<?php

namespace App\Entity;

use App\Repository\ProfilRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ProfilRepository::class)]
#[ORM\Table(name: 'profil')]
#[ORM\Index(columns: ['user_id'])]
#[Gedmo\Loggable]
class Profil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Gedmo\Versioned]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Gedmo\Versioned]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Gedmo\Versioned]
    private ?string $image = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: true)]
    private ?User $user = null;

    public function getId(): ?int { return $this->id; }
    public function getBio(): ?string { return $this->bio; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getImage(): ?string { return $this->image; }
    public function getUser(): ?User { return $this->user; }

    public function setBio(?string $bio): self { $this->bio = $bio; return $this; }
    public function setTelephone(?string $telephone): self { $this->telephone = $telephone; return $this; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
}
