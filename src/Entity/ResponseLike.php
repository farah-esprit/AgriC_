<?php

namespace App\Entity;

use App\Entity\Response;
use App\Entity\User;
use App\Repository\ResponseLikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResponseLikeRepository::class)]
#[ORM\Table(name: 'response_like')]
#[ORM\UniqueConstraint(name: 'unique_response_like', columns: ['response_id', 'user_id'])]
class ResponseLike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Response::class, inversedBy: 'likes')]
    #[ORM\JoinColumn(name: 'response_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Response $response = null;

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

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): static
    {
        $this->response = $response;
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
