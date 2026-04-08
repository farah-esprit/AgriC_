<?php

namespace App\Entity;

use App\Repository\ResponseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResponseRepository::class)]
#[ORM\Table(name: 'response')]
class Response
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu de la réponse est obligatoire.')]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Thread::class, inversedBy: 'responses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Thread $thread = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'responses')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childResponses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Response $parentResponse = null;

    #[ORM\OneToMany(mappedBy: 'parentResponse', targetEntity: self::class, cascade: ['remove'])]
    private Collection $childResponses;

    #[ORM\Column(type: 'integer')]
    private ?int $likeCount = 0;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->childResponses = new ArrayCollection();
        $this->likeCount = 0;
    }

    public function getId(): ?int { return $this->id; }
    public function getContent(): ?string { return $this->content; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function getThread(): ?Thread { return $this->thread; }

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

    public function getParentResponse(): ?Response { return $this->parentResponse; }
    public function getChildResponses(): Collection { return $this->childResponses; }
    public function getLikeCount(): ?int { return $this->likeCount; }

    public function setContent(?string $content): self { $this->content = $content; return $this; }
    public function setCreatedAt(?\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function setThread(?Thread $thread): self { $this->thread = $thread; return $this; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function setParentResponse(?Response $parentResponse): self { $this->parentResponse = $parentResponse; return $this; }
    public function setLikeCount(?int $likeCount): self { $this->likeCount = $likeCount; return $this; }

    public function addChildResponse(Response $childResponse): self
    {
        if (!$this->childResponses->contains($childResponse)) {
            $this->childResponses[] = $childResponse;
            $childResponse->setParentResponse($this);
        }
        return $this;
    }

    public function removeChildResponse(Response $childResponse): self
    {
        if ($this->childResponses->removeElement($childResponse)) {
            if ($childResponse->getParentResponse() === $this) {
                $childResponse->setParentResponse(null);
            }
        }
        return $this;
    }
}
