<?php
namespace App\Repository;
use App\Entity\Thread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
class ThreadRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Thread::class); }

    public function findMostLiked(int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.likeCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findMostCommented(int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.responses', 'r')
            ->groupBy('t.id')
            ->orderBy('COUNT(r.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
