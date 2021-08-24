<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    // /**
    //  * @return Order[] Returns an array of Order objects
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
    public function findOneBySomeField($value): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findInRunningEntity($id,$limit,$offset)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.entity = :id')
            ->andWhere('o.isActive = :act')
            ->andWhere('o.status IN (:status)')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('act', 1)
            ->setParameter('id', $id)
            ->setParameter('status', [Order::STATUS_NEW,Order::STATUS_PAID_ONLY])
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findInRunningAll($limit,$offset)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.isActive = :act')
            ->andWhere('o.status IN (:status)')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('act', 1)
            ->setParameter('status', [Order::STATUS_NEW,Order::STATUS_PAID_ONLY])
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllEntity($id,$limit,$offset)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.entity = :id')
            ->andWhere('o.isActive = :act')
            ->setParameter('act', 1)
            ->setParameter('id', $id)
            ->orderBy('o.id', 'DESC')
            ->orderBy('o.status', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllAdmin($limit,$offset)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.isActive = :act')
            ->setParameter('act', 1)
            ->orderBy('o.id', 'DESC')
            ->orderBy('o.status', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findInRunningRestaurantAdmin($limit,$offset)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.isActive = :act')
            ->andWhere('o.status IN (:status)')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('act', 1)
            ->setParameter('status', [Order::STATUS_NEW,Order::STATUS_PAID_ONLY])
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findPaidEntity($id,$limit,$offset)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.entity = :id')
            ->andWhere('o.isActive = :act')
            ->andWhere('o.status = :status')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('act', 1)
            ->setParameter('id', $id)
            ->setParameter('status',Order::STATUS_PAID_AND_DELIVERED)
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findPaidEntityAdmin($limit,$offset)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.isActive = :act')
            ->andWhere('o.status = :status')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('act', 1)
            ->setParameter('status',Order::STATUS_PAID_AND_DELIVERED)
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findPaidEntityTopManager($id,$limit,$offset)
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.entity','entity')
            ->leftJoin('entity.topManager','top_manager')
            ->andWhere('top_manager.id = :id')
            ->andWhere('o.isActive = :act')
            ->andWhere('o.status = :status')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('id', $id)
            ->setParameter('act', 1)
            ->setParameter('status',Order::STATUS_PAID_AND_DELIVERED)
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
}
