<?php

namespace App\Repository;

use App\Entity\EntityActivation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EntityActivation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EntityActivation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EntityActivation[]    findAll()
 * @method EntityActivation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityActivationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityActivation::class);
    }

    // /**
    //  * @return EntityActivation[] Returns an array of EntityActivation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EntityActivation
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
