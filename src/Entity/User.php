<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
#[UniqueEntity(fields: ['telephone'], message: 'Ce numéro est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'user_id')]
    private ?int $userId = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le nom complet est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[0-9]{8,15}$/',
        message: 'Le numéro doit contenir entre 8 et 15 chiffres.'
    )]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 255, name: 'motDePasse')]
    private ?string $motDePasse = null;

    /**
     * Champ non persisté — utilisé uniquement lors de l'inscription.
     */
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.', groups: ['registration'])]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
        groups: ['registration']
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
        message: 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
        groups: ['registration']
    )]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $role = 'AGRICULTEUR';

    #[ORM\Column(type: 'string', length: 50, nullable: true, name: 'etatCompte')]
    private ?string $etatCompte = 'ACTIF';

    #[ORM\Column(type: 'datetime', nullable: true, name: 'date_creation')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Evenement::class)]
    private Collection $evenements;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Reclamation::class)]
    private Collection $reclamations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Thread::class)]
    private Collection $threads;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Response::class)]
    private Collection $responses;

    public function __construct()
    {
        $this->evenements   = new ArrayCollection();
        $this->reclamations = new ArrayCollection();
        $this->threads      = new ArrayCollection();
        $this->responses    = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    // ════════════════════════════════════════════════════════════
    // GETTERS & SETTERS
    // ════════════════════════════════════════════════════════════

    public function getUserId(): ?int { return $this->userId; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): self { $this->nom = $nom; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): self { $this->telephone = $telephone; return $this; }

    public function getMotDePasse(): ?string { return $this->motDePasse; }
    public function setMotDePasse(?string $motDePasse): self { $this->motDePasse = $motDePasse; return $this; }

    public function getPlainPassword(): ?string { return $this->plainPassword; }
    public function setPlainPassword(?string $plainPassword): self { $this->plainPassword = $plainPassword; return $this; }

    public function getRole(): ?string { return $this->role; }
    public function setRole(?string $role): self { $this->role = $role; return $this; }

    public function getEtatCompte(): ?string { return $this->etatCompte; }
    public function setEtatCompte(?string $etatCompte): self { $this->etatCompte = $etatCompte; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(?\DateTimeInterface $dateCreation): self { $this->dateCreation = $dateCreation; return $this; }

    // ════════════════════════════════════════════════════════════
    // COLLECTIONS — Evenements
    // ════════════════════════════════════════════════════════════

    public function getEvenements(): Collection { return $this->evenements; }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements[] = $evenement;
            $evenement->setOrganisateur($this);
        }
        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        if ($this->evenements->removeElement($evenement)) {
            if ($evenement->getOrganisateur() === $this) {
                $evenement->setOrganisateur(null);
            }
        }
        return $this;
    }

    // ════════════════════════════════════════════════════════════
    // COLLECTIONS — Reclamations
    // ════════════════════════════════════════════════════════════

    public function getReclamations(): Collection { return $this->reclamations; }

    public function addReclamation(Reclamation $reclamation): self
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations[] = $reclamation;
            $reclamation->setUtilisateur($this);
        }
        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): self
    {
        if ($this->reclamations->removeElement($reclamation)) {
            if ($reclamation->getUtilisateur() === $this) {
                $reclamation->setUtilisateur(null);
            }
        }
        return $this;
    }

    // ════════════════════════════════════════════════════════════
    // COLLECTIONS — Threads
    // ════════════════════════════════════════════════════════════

    public function getThreads(): Collection { return $this->threads; }

    public function addThread(Thread $thread): self
    {
        if (!$this->threads->contains($thread)) {
            $this->threads[] = $thread;
            $thread->setUser($this);
        }
        return $this;
    }

    public function removeThread(Thread $thread): self
    {
        if ($this->threads->removeElement($thread)) {
            if ($thread->getUser() === $this) {
                $thread->setUser(null);
            }
        }
        return $this;
    }

    // ════════════════════════════════════════════════════════════
    // COLLECTIONS — Responses
    // ════════════════════════════════════════════════════════════

    public function getResponses(): Collection { return $this->responses; }

    public function addResponse(Response $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setUser($this);
        }
        return $this;
    }

    public function removeResponse(Response $response): self
    {
        if ($this->responses->removeElement($response)) {
            if ($response->getUser() === $this) {
                $response->setUser(null);
            }
        }
        return $this;
    }

    // ════════════════════════════════════════════════════════════
    // MÉTHODES SYMFONY SECURITY
    // ════════════════════════════════════════════════════════════

    /**
     * Identifiant unique pour l'authentification (remplace getUsername()).
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = [$this->role ?: 'ROLE_USER'];
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * Retourne le mot de passe hashé (requis par PasswordAuthenticatedUserInterface).
     */
    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
}
