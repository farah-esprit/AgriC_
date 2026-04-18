<?php

namespace App\Repository;

use App\Entity\Culture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CultureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Culture::class);
    }

    // 📊 Total cultures par utilisateur
    public function countTotal(int $userId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.idCulture)')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // 📊 Superficie moyenne par utilisateur
    public function getSuperficieMoyenne(int $userId): float
    {
        return round((float) $this->createQueryBuilder('c')
            ->select('AVG(c.superficie)')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult(), 2);
    }

    // 📊 Superficie totale par utilisateur
    public function getSuperficieTotal(int $userId): float
    {
        return round((float) $this->createQueryBuilder('c')
            ->select('SUM(c.superficie)')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult(), 2);
    }

    // 📊 Superficie max par utilisateur
    public function getSuperficieMax(int $userId): float
    {
        return (float) $this->createQueryBuilder('c')
            ->select('MAX(c.superficie)')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // 📊 Superficie min par utilisateur
    public function getSuperficieMin(int $userId): float
    {
        return (float) $this->createQueryBuilder('c')
            ->select('MIN(c.superficie)')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // 📊 Nombre de cultures par type pour un utilisateur
    public function countParType(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.type AS type, COUNT(c.idCulture) AS total')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('c.type')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 🔍 Recherche globale par utilisateur
    public function search(string $query, int $userId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :userId')
            ->andWhere('(c.nom LIKE :q OR c.localisation LIKE :q OR c.type LIKE :q)')
            ->setParameter('userId', $userId)
            ->setParameter('q', "%$query%")
            ->orderBy('c.idCulture', 'DESC')
            ->getQuery();
    }

    // 🔍 Filtrer par type pour un utilisateur
    public function filterByType(string $type, int $userId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :userId')
            ->andWhere('c.type = :type')
            ->setParameter('userId', $userId)
            ->setParameter('type', $type)
            ->getQuery();
    }

    // 🔽🔼 Trier par superficie pour un utilisateur
    public function orderBySuperficie(int $userId, string $order = 'DESC')
    {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        return $this->createQueryBuilder('c')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.superficie', $order)
            ->getQuery();
    }

    // 📄 Liste globale paginable par utilisateur
    public function findAllByUser(int $userId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('c.idCulture', 'DESC')
            ->getQuery();
    }

    // 📅 Bonus : cultures récentes
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.idCulture', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}