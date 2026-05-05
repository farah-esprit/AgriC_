<?php
namespace App\Repository;
use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Reclamation::class); }

    /**
     * Recherche et tri des réclamations
     * @return Reclamation[]
     */
    public function findBySearchAndSort(?string $search, ?string $sort = 'dateCreation', string $direction = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('r');

        if ($search) {
            $escapedSearch = addcslashes($search, '%_');
            $qb->andWhere('r.objet LIKE :search OR r.description LIKE :search')
               ->setParameter('search', '%' . $escapedSearch . '%');
        }

        // Sécurisation du tri
        $validSorts = ['dateCreation', 'priorite', 'statut', 'objet'];
        $sort = in_array($sort, $validSorts) ? $sort : 'dateCreation';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('r.' . $sort, $direction);

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques globales par statut
     * @return array<int, array<string, mixed>>
     */
    public function countByStatus(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.statut, COUNT(r.idReclamation) as count')
            ->groupBy('r.statut')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques globales par priorité
     * @return array<int, array<string, mixed>>
     */
    public function countByPriority(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.priorite, COUNT(r.idReclamation) as count')
            ->groupBy('r.priorite')
            ->getQuery()
            ->getResult();
    }
}
