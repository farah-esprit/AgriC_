<?php

namespace App\Entity;

use App\Repository\StoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: StoryRepository::class)]
class Story
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $mediaUrl = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'story', targetEntity: StoryLike::class, cascade: ['persist', 'remove'])]
    private Collection $likes;

    #[ORM\OneToMany(mappedBy: 'story', targetEntity: StoryComment::class, cascade: ['persist', 'remove'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'story', targetEntity: StoryView::class, cascade: ['persist', 'remove'])]
    private Collection $views;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->views = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(string $mediaUrl): static
    {
        $this->mediaUrl = $mediaUrl;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, StoryLike>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(StoryLike $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setStory($this);
        }

        return $this;
    }

    public function removeLike(StoryLike $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getStory() === $this) {
                $like->setStory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StoryComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(StoryComment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setStory($this);
        }

        return $this;
    }

    public function removeComment(StoryComment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getStory() === $this) {
                $comment->setStory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StoryView>
     */
    public function getViews(): Collection
    {
        return $this->views;
    }
}
