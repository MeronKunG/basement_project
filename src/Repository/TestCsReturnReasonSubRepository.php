<?php

namespace App\Repository;

use App\Entity\TestCsReturnReasonSub;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TestCsReturnReasonSub|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestCsReturnReasonSub|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestCsReturnReasonSub[]    findAll()
 * @method TestCsReturnReasonSub[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestCsReturnReasonSubRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestCsReturnReasonSub::class);
    }

    public function getReasonSubByReasonId($reasonId)
    {
        return $this->createQueryBuilder('r')
            ->select('r.subReasonCode, r.subReasonTh')
            ->where('r.mainReasonCode = :mainReasonCode')
            ->setParameter('mainReasonCode', $reasonId)
            ->getQuery()
            ->getResult();
    }
}
