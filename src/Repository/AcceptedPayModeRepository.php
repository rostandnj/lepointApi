<?php

namespace App\Repository;

use App\Entity\AcceptedPayMode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AcceptedPayMode|null find($id, $lockMode = null, $lockVersion = null)
 * @method AcceptedPayMode|null findOneBy(array $criteria, array $orderBy = null)
 * @method AcceptedPayMode[]    findAll()
 * @method AcceptedPayMode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AcceptedPayModeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AcceptedPayMode::class);
    }

    // /**
    //  * @return AcceptedPayMode[] Returns an array of AcceptedPayMode objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AcceptedPayMode
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
