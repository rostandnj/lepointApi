<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function search($entity,$type,$city,$name,$limit,$offset)
    {
        $q = $this->createQueryBuilder('p')
            ->leftJoin('p.entity','entity')
            ->leftJoin('entity.location','loc');
        $q->andWhere('entity.type = :type');


        if($name !=""){
            $q->andWhere('p.name LIKE :name');
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

        $q->orderBy('p.name','ASC');

        if($name !=""){
            $q->setParameter('name','%'.$name.'%');
        }

        if($city !=""){
            $q->setParameter('city',$city);
        }

        if($entity !=""){
            $q->setParameter('entity',$entity);
        }


        //$q->setFirstResult($offset)
        //->setMaxResults($limit);


        return $q->getQuery()
            ->getResult()
            ;
    }
}
