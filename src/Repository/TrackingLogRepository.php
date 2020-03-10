<?php

namespace App\Repository;

use App\Entity\TrackingLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TrackingLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrackingLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrackingLog[]    findAll()
 * @method TrackingLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackingLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TrackingLog::class);
    }

    public function getRemarksAndUserByMailCode($mailCode)
    {
        return $this->createQueryBuilder('t')
            ->select('t.remarks, t.user, t.tstamp')
            ->andWhere('t.mailcode = :mailcode')
            ->setParameter('mailcode', $mailCode)
            ->orderBy('t.tstamp', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
