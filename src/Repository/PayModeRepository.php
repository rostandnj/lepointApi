<?php

namespace App\Repository;

use App\Entity\PayMode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PayMode|null find($id, $lockMode = null, $lockVersion = null)
 * @method PayMode|null findOneBy(array $criteria, array $orderBy = null)
 * @method PayMode[]    findAll()
 * @method PayMode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PayModeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PayMode::class);
    }

    // /**
    //  * @return PayMode[] Returns an array of PayMode objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PayMode
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
