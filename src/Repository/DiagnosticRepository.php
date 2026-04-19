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

    // 📊 Total diagnostics par utilisateur
    public function countTotal(int $userId): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.idDiagnostic)')
            ->join('d.culture', 'c')
            ->where('c.user = :userId')
            ->setParameter('userId', $userId)
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
public function findFiltered(?string $search, ?string $cultureId, ?string $dateDebut, ?string $dateFin): array
{
    $qb = $this->createQueryBuilder('d')
        ->leftJoin('d.culture', 'c')
        ->addSelect('c')
        ->orderBy('d.dateDiagnostic', 'DESC');

    if ($search) {
        $qb->andWhere('d.symptomes LIKE :search OR d.informationsComplementaires LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    if ($cultureId) {
        $qb->andWhere('c.idCulture = :culture')
           ->setParameter('culture', $cultureId);
    }

    if ($dateDebut) {
        $qb->andWhere('d.dateDiagnostic >= :dateDebut')
           ->setParameter('dateDebut', new \DateTime($dateDebut));
    }

    if ($dateFin) {
        $qb->andWhere('d.dateDiagnostic <= :dateFin')
           ->setParameter('dateFin', new \DateTime($dateFin));
    }

    return $qb->getQuery()->getResult();
}

public function findFilteredByUser(int $userId, ?string $search, ?string $cultureId, ?string $dateDebut, ?string $dateFin): array
{
    $qb = $this->createQueryBuilder('d')
        ->join('d.culture', 'c')
        ->addSelect('c')
        ->where('c.user = :userId')
        ->setParameter('userId', $userId)
        ->orderBy('d.dateDiagnostic', 'DESC');

    if ($search) {
        $qb->andWhere('(d.symptomes LIKE :search OR d.informationsComplementaires LIKE :search)')
           ->setParameter('search', '%' . $search . '%');
    }

    if ($cultureId) {
        $qb->andWhere('c.idCulture = :culture')
           ->setParameter('culture', $cultureId);
    }

    if ($dateDebut) {
        $qb->andWhere('d.dateDiagnostic >= :dateDebut')
           ->setParameter('dateDebut', new \DateTime($dateDebut));
    }

    if ($dateFin) {
        $qb->andWhere('d.dateDiagnostic <= :dateFin')
           ->setParameter('dateFin', new \DateTime($dateFin));
    }

    return $qb->getQuery()->getResult();
}
public function getStatsByUser(int $userId): array
{
    $parCulture = $this->createQueryBuilder('d')
        ->select('IDENTITY(d.culture) as idCulture, COUNT(d.idDiagnostic) as total')
        ->join('d.culture', 'c')
        ->where('c.user = :userId')
        ->setParameter('userId', $userId)
        ->groupBy('d.culture')
        ->getQuery()
        ->getResult();

    $parMois = $this->createQueryBuilder('d')
        ->select("DATE_FORMAT(d.dateDiagnostic, '%Y-%m') as mois, COUNT(d.idDiagnostic) as total")
        ->join('d.culture', 'c')
        ->where('c.user = :userId')
        ->setParameter('userId', $userId)
        ->groupBy('mois')
        ->orderBy('mois', 'ASC')
        ->getQuery()
        ->getResult();

    $total = $this->countTotal($userId);

    return [
        'total'      => $total,
        'parCulture' => $parCulture,
        'parMois'    => $parMois,
    ];
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