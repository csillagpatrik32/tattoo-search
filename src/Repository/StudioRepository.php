<?php

namespace App\Repository;

use App\Entity\Studio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Studio|null find($id, $lockMode = null, $lockVersion = null)
 * @method Studio|null findOneBy(array $criteria, array $orderBy = null)
 * @method Studio[]    findAll()
 * @method Studio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudioRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Studio::class);
    }

    /**
     * @return Studio[] Returns an array of Studio objects
     */
    public function findByCityAndStyle($city, $style)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.address', 'a')
            ->addSelect('a')
            ->innerJoin('s.style', 'st')
            ->addSelect('st')
            ->andWhere('a.city = :city')
            ->andWhere('st.name = :style')
            ->setParameter('city', $city)
            ->setParameter('style', $style)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Studio
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
