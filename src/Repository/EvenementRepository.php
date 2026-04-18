<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * Recherche et tri des événements
     */
    public function findBySearchAndSort(?string $search, ?string $sort = 'dateDebut', string $direction = 'ASC')
    {
        $qb = $this->createQueryBuilder('e');

        if ($search) {
            $qb->andWhere('e.titre LIKE :search OR e.description LIKE :search OR e.lieu LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Sécurisation du tri
        $validSorts = ['dateDebut', 'titre', 'lieu', 'capaciteMax'];
        $sort = in_array($sort, $validSorts) ? $sort : 'dateDebut';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $qb->orderBy('e.' . $sort, $direction);

        return $qb->getQuery()->getResult();
    }
}