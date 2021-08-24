<?php

namespace App\Repository;

use App\Entity\Entity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entity[]    findAll()
 * @method Entity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entity::class);
    }

    // /**
    //  * @return Entity[] Returns an array of Entity objects
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
    public function findOneBySomeField($value): ?Entity
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findManagersOfOwner($ownerId,$userId)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.managers','managers')
            ->leftJoin('e.owner','owner')
            ->andWhere('owner.id = :owner_id')
            ->andWhere('managers.id = :user_id')
            ->andWhere('e.isActive = :act')
            ->setParameter('owner_id', $ownerId)
            ->setParameter('user_id', $userId)
            ->setParameter('act', 1)
            //->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findEntityManageByUser($entityId,$userId)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.managers','managers')
            ->andWhere('e.id = :entity_id')
            ->andWhere('managers.id = :user_id')
            ->andWhere('e.isActive = :act')
            ->setParameter('entity_id', $entityId)
            ->setParameter('user_id', $userId)
            ->setParameter('act', 1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findOnlyEntityManageByUser($userId, $type,$limit,$offset)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.managers','m')
            ->andWhere('m.id = :user_id')
            ->andWhere('e.isActive = :act')
            ->andWhere('e.type = :type')
            ->setParameter('user_id', $userId)
            ->setParameter('type', $type)
            ->setParameter('act', 1)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }

    public function searchEntities($type,$city,$name,$limit,$offset)
    {
        $q = $this->createQueryBuilder('e')->leftJoin('e.globalInfo','info');
        if($city != ""){
            $q->leftJoin('e.location','loc');
        }

        if($name != ""){
            $q->andWhere('info.name LIKE :name');
        }

        if($city != ""){
            $q->andWhere('loc.city = :city');
        }

        $q->andWhere('e.isActive = :act')->andWhere('e.type = :type')
        ->setParameter('act', 1)
        ->setParameter('type', $type);

        if($name != ""){
            $q->setParameter('name', '%'.$name.'%');
        }
        if($city != ""){
            $q->setParameter('city', $city);
        }

        $q->setFirstResult($offset)->setMaxResults($limit);

        return $q->getQuery()
            ->getResult();
    }

    public function findByAdminAll($type,$limit,$offset)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.type = :type')
            ->andWhere('e.isActive = :act')
            ->setParameter('type', $type)
            ->setParameter('act', 1)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('e.id','DESC')
            ->getQuery()
            ->getResult()
            ;
    }

}
