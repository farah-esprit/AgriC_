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

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $userId = null;

    public function getId(): ?int { return $this->id; }
    public function getBio(): ?string { return $this->bio; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getImage(): ?string { return $this->image; }
    public function getUserId(): ?int { return $this->userId; }

    public function setBio(?string $bio): self { $this->bio = $bio; return $this; }
    public function setTelephone(?string $telephone): self { $this->telephone = $telephone; return $this; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }
    public function setUserId(?int $userId): self { $this->userId = $userId; return $this; }
}
