<?php

namespace App\Repository;

use App\Entity\Thread;
use App\Entity\ThreadLike;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ThreadLike>
 */
class ThreadLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThreadLike::class);
    }

    public function existsLike(User $user, Thread $thread): bool
    {
        return (bool) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.user = :user')
            ->andWhere('l.thread = :thread')
            ->setParameter('user', $user)
            ->setParameter('thread', $thread)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLike(User $user, Thread $thread): ?ThreadLike
    {
        return $this->findOneBy([
            'user' => $user,
            'thread' => $thread,
        ]);
    }
}
