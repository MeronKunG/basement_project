<?php

namespace App\Repository;

use App\Entity\TestCSRemarksProcess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TestCSRemarksProcess|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestCSRemarksProcess|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestCSRemarksProcess[]    findAll()
 * @method TestCSRemarksProcess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestCSRemarksProcessRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestCSRemarksProcess::class);
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
