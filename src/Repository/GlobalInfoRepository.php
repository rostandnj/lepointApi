<?php

namespace App\Repository;

use App\Entity\GlobalInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GlobalInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalInfo[]    findAll()
 * @method GlobalInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GlobalInfo::class);
    }

    // /**
    //  * @return GlobalInfo[] Returns an array of GlobalInfo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GlobalInfo
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
