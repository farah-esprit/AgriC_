<?php
namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function findAllWithProduit(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.produit', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getResult();
    }
}