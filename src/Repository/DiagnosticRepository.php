<?php

namespace App\Repository;

use App\Entity\Diagnostic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DiagnosticRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Diagnostic::class);
    }

    // 📊 Total diagnostics
    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.idDiagnostic)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // 📊 Nombre par culture
    public function countParCulture(): array
    {
        return $this->createQueryBuilder('d')
            ->select('IDENTITY(d.culture) AS idCulture, COUNT(d.idDiagnostic) AS total')
            ->groupBy('d.culture')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 📊 Nombre par mois
    public function countParMois(): array
    {
        return $this->createQueryBuilder('d')
            ->select("SUBSTRING(d.dateDiagnostic, 1, 7) AS mois, COUNT(d.idDiagnostic) AS total")
            ->groupBy('mois')
            ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // 🔍 Recherche globale
    public function search(string $query): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.symptomes LIKE :q')
            ->orWhere('d.informationsComplementaires LIKE :q')
            ->orWhere('d.dateDiagnostic LIKE :q')
            ->setParameter('q', "%$query%")
            ->orderBy('d.dateDiagnostic', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 🔍 Filtrer par culture
    public function filterByCulture(int $idCulture): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.culture = :id')
            ->setParameter('id', $idCulture)
            ->orderBy('d.dateDiagnostic', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 📅 Diagnostics récents
    public function getRecents(int $limit = 5): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.dateDiagnostic', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // 📅 Filtrer par intervalle de dates
    public function filterByDateRange(string $dateDebut, string $dateFin): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.dateDiagnostic BETWEEN :start AND :end')
            ->setParameter('start', $dateDebut)
            ->setParameter('end', $dateFin)
            ->orderBy('d.dateDiagnostic', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 🚀 Derniers diagnostics par culture
    public function findLatestByCulture(int $idCulture, int $limit = 5): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.culture = :culture')
            ->setParameter('culture', $idCulture)
            ->orderBy('d.dateDiagnostic', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}