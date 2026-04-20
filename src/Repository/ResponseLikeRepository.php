<?php

namespace App\Repository;

use App\Entity\Response;
use App\Entity\ResponseLike;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResponseLike>
 */
class ResponseLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResponseLike::class);
    }

    public function existsLike(User $user, Response $response): bool
    {
        return (bool) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.user = :user')
            ->andWhere('l.response = :response')
            ->setParameter('user', $user)
            ->setParameter('response', $response)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLike(User $user, Response $response): ?ResponseLike
    {
        return $this->findOneBy([
            'user' => $user,
            'response' => $response,
        ]);
    }
}
