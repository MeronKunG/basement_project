<?php

namespace App\Repository;

use App\Entity\TestEcomToPeakLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TestEcomToPeakLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestEcomToPeakLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestEcomToPeakLog[]    findAll()
 * @method TestEcomToPeakLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestEcomToPreakLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestEcomToPeakLog::class);
    }

    public function getDataEcomByMailCode($mailCode)
    {
        return $this->createQueryBuilder('p')
            ->select('p.parentId, p.totalQty, p.unitPrice, g.productname, c.catName, mbd.sendmaildate')
            ->leftjoin('App\Entity\MerchantBillingDelivery', 'mbd', 'WITH','p.mailcode = mbd.mailcode')
            ->leftJoin('App\Entity\TestCsReturnCollect', 'r', 'WITH', 'p.mailcode = r.mailcode')
            ->leftJoin('App\Entity\TestCsReturnCat', 'c', 'WITH', 'r.catId = c.catId')
            ->leftJoin('App\Entity\GlobalProduct', 'g', 'WITH', 'p.parentId = g.productid')
            ->where('p.mailcode =:mailCode')
            ->setParameter('mailCode', $mailCode)
            ->getQuery()
            ->getResult()
            ;
    }
}
