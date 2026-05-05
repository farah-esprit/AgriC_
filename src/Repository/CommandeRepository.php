<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    // ✅ Sauvegarde (bonne pratique Symfony 6)
    public function save(Commande $commande, bool $flush = false): void
    {
        $this->getEntityManager()->persist($commande);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // ✅ Suppression
    public function remove(Commande $commande, bool $flush = false): void
    {
        $this->getEntityManager()->remove($commande);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // 📊 Commandes récentes
    /** @return Commande[] */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.idCommande', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // 🔍 Trouver toutes les commandes avec relations (évite N+1)
    /** @return Commande[] */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.produit', 'p')
            ->addSelect('p')
            ->leftJoin('c.user', 'u')
            ->addSelect('u')
            ->orderBy('c.idCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 🔍 Trouver commandes par utilisateur
    /** @return Commande[] */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->setParameter('user', $userId)
            ->orderBy('c.idCommande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 📈 Compter commandes
    public function countAll(): int
    {
        return $this->count([]);
    }

    // 💰 Total des commandes (ex: dashboard)
    public function getTotalRevenue(): float
    {
        return (float) $this->createQueryBuilder('c')
            ->select('SUM(c.total)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}