<?php

namespace App\Repository;

use App\Entity\TestCsRemarksStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TestCsRemarksStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestCsRemarksStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestCsRemarksStatus[]    findAll()
 * @method TestCsRemarksStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestCSRemarksStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestCsRemarksStatus::class);
    }

    // /**
    //  * @return TestCSRemarksProcess[] Returns an array of TestCSRemarksProcess objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TestCSRemarksProcess
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
