<?php

namespace App\Repository;

use App\Entity\Favorite;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\VarDumper\Dumper\esc;

/**
 * @method Favorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Favorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Favorite[]    findAll()
 * @method Favorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favorite::class);
    }

    // /**
    //  * @return Favorite[] Returns an array of Favorite objects
    //  */

    public function findAllSortByName(User $user)
    {

        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :val')
            ->setParameter('val', $user)
            ->orderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }  public function findAllSortByCategory(User $user)
    {

        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :val')
            ->setParameter('val', $user)
            ->join('f.category','category')
            ->orderBy('category.name', 'ASC')
            ->addOrderBy('f.name','ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?Favorite
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
