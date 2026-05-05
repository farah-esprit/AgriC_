<?php
namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    /**
     * @return Stock[]
     */
    public function findAllWithProduit(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.produit', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getResult();
    }
}