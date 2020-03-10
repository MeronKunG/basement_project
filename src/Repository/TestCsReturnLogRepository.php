<?php

namespace App\Repository;

use App\Entity\TestCsReturnLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TestCsReturnLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestCsReturnLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestCsReturnLog[]    findAll()
 * @method TestCsReturnLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestCsReturnLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestCsReturnLog::class);
    }
}
