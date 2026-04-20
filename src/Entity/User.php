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

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $resetTokenRequestedAt = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $role = 'AGRICULTEUR';

    #[ORM\Column(type: 'string', length: 50, nullable: true, name: 'etatCompte')]
    private ?string $etatCompte = 'ACTIF';

    #[ORM\Column(type: 'datetime', nullable: true, name: 'date_creation')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $strikes = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $xp = 0;

    #[ORM\ManyToMany(targetEntity: Thread::class)]
    #[ORM\JoinTable(name: 'user_shared_threads')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id')]
    #[ORM\InverseJoinColumn(name: 'thread_id', referencedColumnName: 'id')]
    private Collection $sharedThreads;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Evenement::class)]
    private Collection $evenements;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Reclamation::class)]
    private Collection $reclamations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Thread::class)]
    private Collection $threads;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Response::class)]
    private Collection $responses;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Friendship::class)]
    private Collection $friendshipsSent;

    #[ORM\OneToMany(mappedBy: 'friend', targetEntity: Friendship::class)]
    private Collection $friendshipsReceived;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: ChatMessage::class)]
    private Collection $messagesSent;

    #[ORM\OneToMany(mappedBy: 'recipient', targetEntity: ChatMessage::class)]
    private Collection $messagesReceived;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Profil::class, cascade: ['persist', 'remove'])]
    private ?Profil $profile = null;

    public function __construct()
    {
        $this->evenements = new ArrayCollection();
        $this->reclamations = new ArrayCollection();
        $this->threads = new ArrayCollection();
        $this->responses = new ArrayCollection();
        $this->commandes = new ArrayCollection();
        $this->friendshipsSent = new ArrayCollection();
        $this->friendshipsReceived = new ArrayCollection();
        $this->messagesSent = new ArrayCollection();
        $this->messagesReceived = new ArrayCollection();
        $this->sharedThreads = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(?string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getEtatCompte(): ?string
    {
        return $this->etatCompte;
    }

    public function setEtatCompte(?string $etatCompte): self
    {
        $this->etatCompte = $etatCompte;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

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

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
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

    /**
     * @return Collection<int, Reclamation>
     */
    public function getReclamations(): Collection
    {
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): self
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations->add($reclamation);
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

    /**
     * @return Collection<int, Thread>
     */
    public function getThreads(): Collection
    {
        return $this->threads;
    }

    public function addThread(Thread $thread): self
    {
        if (!$this->threads->contains($thread)) {
            $this->threads->add($thread);
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

    /**
     * @return Collection<int, Response>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(Response $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses->add($response);
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

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setUser($this);
        }
        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            if ($commande->getUser() === $this) {
                $commande->setUser(null);
            }
        }
        return $this;
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

    public function getFriendshipsSent(): Collection { return $this->friendshipsSent; }
    public function getFriendshipsReceived(): Collection { return $this->friendshipsReceived; }
    public function getMessagesSent(): Collection { return $this->messagesSent; }
    public function getMessagesReceived(): Collection { return $this->messagesReceived; }

    public function getProfile(): ?Profil
    {
        return $this->profile;
    }

    public function setProfile(?Profil $profile): self
    {
        // set the owning side of the relation if necessary
        if ($profile !== null && $profile->getUser() !== $this) {
            $profile->setUser($this);
        }

        $this->profile = $profile;

        return $this;
    }

    public function getStrikes(): int
    {
        return $this->strikes;
    }

    public function setStrikes(int $strikes): self
    {
        $this->strikes = $strikes;
        return $this;
    }

    public function getXp(): int
    {
        return $this->xp;
    }

    public function setXp(int $xp): self
    {
        $this->xp = $xp;
        return $this;
    }

    /**
     * @return Collection<int, Thread>
     */
    public function getSharedThreads(): Collection
    {
        return $this->sharedThreads;
    }

    public function addSharedThread(Thread $thread): self
    {
        if (!$this->sharedThreads->contains($thread)) {
            $this->sharedThreads->add($thread);
        }
        return $this;
    }

    public function removeSharedThread(Thread $thread): self
    {
        $this->sharedThreads->removeElement($thread);
        return $this;
    }

    /**
     * Retourne le grade basé sur l'XP
     */
    public function getRank(): string
    {
        if ($this->xp >= 101) return 'Master 👑';
        if ($this->xp >= 51) return 'Expert 🔬';
        if ($this->xp >= 11) return 'Fermier 🚜';
        return 'Novice 🌾';
    }

    public function getRankColor(): string
    {
        if ($this->xp >= 101) return 'dark';
        if ($this->xp >= 51) return 'danger';
        if ($this->xp >= 11) return 'info';
        return 'success';
    }
}
