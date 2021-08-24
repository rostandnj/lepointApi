<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function loadUserByUsername($usernameOrEmail)
    {


        return $this->createQueryBuilder('u')
            ->where('u.email = :query')
            //->having("isClose = :close")
            //->andHaving("isActive = :active")
            //->andHaving("isValid = :active")
            ->setParameter('query', $usernameOrEmail)
            //->setParameter('active', 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllUser($limit,$offset)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.type <> :val')
            ->setParameter('val', User::USER_ADMIN)
            ->orderBy('u.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findClientAndOwner($limit,$offset)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.type IN (:val)')
            ->setParameter('val', [User::USER_OWNER,User::USER_CLIENT])
            ->orderBy('u.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findManager($limit,$offset)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.type IN (:val)')
            ->setParameter('val', [User::USER_MANAGER])
            ->orderBy('u.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findTopManager($limit,$offset)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.type IN (:val)')
            ->setParameter('val', [User::USER_TOP_MANAGER])
            ->orderBy('u.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }
}
