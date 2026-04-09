<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table(name: 'admin')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class Admin implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $resetTokenRequestedAt = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): self { $this->prenom = $prenom; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): self { $this->telephone = $telephone; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }
// Ajoute juste avant la fin de la classe Admin

#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $photo = null;

public function getPhoto(): ?string
{
    return $this->photo;
}

public function setPhoto(?string $photo): self
{
    $this->photo = $photo;
    return $this;
}
    /**
     * Identifiant unique de l'utilisateur (remplace getUsername() en Symfony 6)
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }

    public function eraseCredentials(): void
    {
        // Rien à faire ici
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetTokenRequestedAt(): ?\DateTimeImmutable
    {
        return $this->resetTokenRequestedAt;
    }

    public function setResetTokenRequestedAt(?\DateTimeImmutable $resetTokenRequestedAt): self
    {
        $this->resetTokenRequestedAt = $resetTokenRequestedAt;
        return $this;
    }
}
