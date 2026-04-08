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

    // 📊 Total cultures
    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.idCulture)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // 📊 Superficie moyenne
    public function getSuperficieMoyenne(): float
    {
        return round((float) $this->createQueryBuilder('c')
            ->select('AVG(c.superficie)')
            ->getQuery()
            ->getSingleScalarResult(), 2);
    }

    // 📊 Superficie totale
    public function getSuperficieTotal(): float
    {
        return round((float) $this->createQueryBuilder('c')
            ->select('SUM(c.superficie)')
            ->getQuery()
            ->getSingleScalarResult(), 2);
    }

    // 📊 Superficie max
    public function getSuperficieMax(): float
    {
        return (float) $this->createQueryBuilder('c')
            ->select('MAX(c.superficie)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // 📊 Superficie min
    public function getSuperficieMin(): float
    {
        return (float) $this->createQueryBuilder('c')
            ->select('MIN(c.superficie)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // 📊 Nombre de cultures par type
    public function countParType(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.type AS type, COUNT(c.idCulture) AS total')
            ->groupBy('c.type')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 🔍 Recherche globale
    public function search(string $query): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.nom LIKE :q')
            ->orWhere('c.localisation LIKE :q')
            ->orWhere('c.type LIKE :q')
            ->setParameter('q', "%$query%")
            ->orderBy('c.idCulture', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 🔍 Filtrer par type
    public function filterByType(string $type): array
    {
        return $this->findBy(['type' => $type]);
    }

    // 🔽🔼 Trier par superficie
    public function orderBySuperficie(string $order = 'DESC'): array
    {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        return $this->createQueryBuilder('c')
            ->orderBy('c.superficie', $order)
            ->getQuery()
            ->getResult();
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