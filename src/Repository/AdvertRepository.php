<?php

namespace App\Repository;

use App\Entity\Advert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Advert|null find($id, $lockMode = null, $lockVersion = null)
 * @method Advert|null findOneBy(array $criteria, array $orderBy = null)
 * @method Advert[]    findAll()
 * @method Advert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdvertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advert::class);
    }

    // /**
    //  * @return Advert[] Returns an array of Advert objects
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
    public function findOneBySomeField($value): ?Advert
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function search($entity,$type,$city,$name,$limit,$offset)
    {
        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.entity','entity')
            ->leftJoin('entity.location','loc');
        $q->andWhere('entity.type = :type');


        if($name !=""){
            $q->andWhere('p.title LIKE :name');
        }

        if($city !=""){
            $q->andWhere('loc.city = :city');
        }

        if($entity !=""){
            $q->andWhere('entity.id = :entity');
        }

        $q->andWhere('p.isActive = :act')
            ->setParameter('act', 1)
            ->setParameter('type', $type)
        ;

        $q->orderBy('p.title','ASC');

        if($name !=""){
            $q->setParameter('name','%'.$name.'%');
        }

        if($city !=""){
            $q->setParameter('city',$city);
        }

        if($entity !=""){
            $q->setParameter('entity',$entity);
        }


        $q->setFirstResult($offset)
        ->setMaxResults($limit);


        return $q->getQuery()
            ->getResult()
            ;
    }
}
