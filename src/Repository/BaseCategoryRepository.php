<?php

namespace App\Repository;

use App\Entity\BaseCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BaseCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method BaseCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method BaseCategory[]    findAll()
 * @method BaseCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BaseCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseCategory::class);
    }

    // /**
    //  * @return BaseCategory[] Returns an array of BaseCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BaseCategory
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
