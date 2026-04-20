<?php

namespace App\Entity;

use App\Entity\Thread;
use App\Entity\User;
use App\Repository\ThreadLikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadLikeRepository::class)]
#[ORM\Table(name: 'thread_like')]
#[ORM\UniqueConstraint(name: 'unique_thread_like', columns: ['thread_id', 'user_id'])]
class ThreadLike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Thread::class, inversedBy: 'likes')]
    #[ORM\JoinColumn(name: 'thread_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Thread $thread = null;

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    private ?\DateTimeInterface $createdAt = null;

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

    public function getThread(): ?Thread
    {
        return $this->thread;
    }

    public function setThread(?Thread $thread): static
    {
        $this->thread = $thread;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
