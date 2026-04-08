<?php

namespace App\Entity;

use App\Repository\ThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ThreadRepository::class)]
#[ORM\Table(name: 'thread')]
class Thread
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(min: 5, max: 100, minMessage: 'Le titre doit faire au moins {{ limit }} caractères.')]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu est obligatoire.')]
    #[Assert\Length(min: 10, minMessage: 'Le contenu doit faire au moins {{ limit }} caractères.')]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $status = 'ACTIVE';

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'threads')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $category = 'Général';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $tags = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $attachments = null;

    #[ORM\Column(type: 'integer')]
    private ?int $likeCount = 0;

    #[ORM\OneToMany(mappedBy: 'thread', targetEntity: Response::class, cascade: ['remove'])]
    private Collection $responses;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->responses = new ArrayCollection();
        $this->likeCount = 0;
        $this->status = 'ACTIVE';
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function getContent(): ?string { return $this->content; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function getStatus(): ?string { return $this->status; }

    public function getUser(): ?User
    {
        try {
            if ($this->user) {
                $this->user->getNom();
            }
            return $this->user;
        } catch (\Doctrine\ORM\EntityNotFoundException $e) {
            $dummyUser = new User();
            $dummyUser->setNom('Utilisateur supprimé');
            $dummyUser->setRole('INCONNU');
            return $dummyUser;
        }
    }

    public function getAuthorName(): string
    {
        $user = $this->getUser();
        return $user ? (string) $user->getNom() : 'Utilisateur supprimé';
    }

    public function getCategory(): ?string { return $this->category; }
    public function getTags(): ?string { return $this->tags; }
    public function getAttachments(): ?string { return $this->attachments; }
    public function getLikeCount(): ?int { return $this->likeCount; }
    public function getResponses(): Collection { return $this->responses; }

    public function setTitle(?string $title): self { $this->title = $title; return $this; }
    public function setContent(?string $content): self { $this->content = $content; return $this; }
    public function setCreatedAt(?\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function setStatus(?string $status): self { $this->status = $status; return $this; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function setCategory(?string $category): self { $this->category = $category; return $this; }
    public function setTags(?string $tags): self { $this->tags = $tags; return $this; }
    public function setAttachments(?string $attachments): self { $this->attachments = $attachments; return $this; }
    public function setLikeCount(?int $likeCount): self { $this->likeCount = $likeCount; return $this; }

    public function addResponse(Response $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setThread($this);
        }
        return $this;
    }

    public function removeResponse(Response $response): self
    {
        if ($this->responses->removeElement($response)) {
            if ($response->getThread() === $this) {
                $response->setThread(null);
            }
        }
        return $this;
    }
}
