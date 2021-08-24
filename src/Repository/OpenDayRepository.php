<?php

namespace App\Repository;

use App\Entity\OpenDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OpenDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method OpenDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method OpenDay[]    findAll()
 * @method OpenDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OpenDayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OpenDay::class);
    }

    // /**
    //  * @return OpenDay[] Returns an array of OpenDay objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OpenDay
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
