<?php

namespace App\Repository;

use App\Entity\TestCsReturnCollect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TestCsReturnCollect|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestCsReturnCollect|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestCsReturnCollect[]    findAll()
 * @method TestCsReturnCollect[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestCsReturnCollectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestCsReturnCollect::class);
    }

    // /**
    //  * @return MerchantBillingDelivery[] Returns an array of MerchantBillingDelivery objects
    //  */

    public function getRecordStatus()
    {
        return $this->createQueryBuilder('t')
            ->select('t.systemReturnDate, count(t.mailcode) AS count')
            ->where('t.recordStatus = 1')
            ->groupBy('t.systemReturnDate')
            ->getQuery()
            ->getResult();
    }

    public function getMailCodeByStatus($dateTime)
    {
        return $this->createQueryBuilder('t')
            ->select('t.mailcode, t.systemReturnDate, m.takeorderby, m.paymentInvoice, m.transporterId, m.codPrice, m.expenseDiscount, m.sendmaildate')
            ->leftJoin('App\Entity\MerchantBillingDelivery', 'm', 'WITH', 't.mailcode = m.mailcode')
            ->where('t.recordStatus = 1 AND DATE_FORMAT(t.systemReturnDate, \'%Y-%m-%d\') = :dateTime')
            ->setParameter('dateTime', $dateTime)
            ->orderBy('t.systemReturnDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getMailCodeAndIdByMailCode($mailCode)
    {
        return $this->createQueryBuilder('aa')
            ->select('aa.recordId, aa.mailcode, bb.takeorderby, bb.paymentInvoice, bb.transporterId, bb.codPrice, bb.expenseDiscount, bb.sendmaildate')
            ->leftJoin('App\Entity\MerchantBillingDelivery', 'bb', 'WITH', 'aa.mailcode = bb.mailcode')
            ->leftJoin('App\Entity\MerchantConfig', 'cc', 'WITH', 'bb.takeorderby = cc.takeorderby')
            ->where('aa.mailcode =:mailCode')
            ->andWhere('aa.catId is null AND cc.merType IN (\'holding\', \'afa\') and DATE(aa.systemReturnDate) between \'2019-11-01\' and \'2019-11-30\'')
            ->setParameter('mailCode', $mailCode)
            ->getQuery()
            ->getResult();
    }

    public function getMailCodeAndIdByMerTypeAndDate()
    {
        return $this->createQueryBuilder('aa')
            ->select('aa.recordId, aa.mailcode')
            ->leftJoin('App\Entity\MerchantBillingDelivery', 'bb', 'WITH', 'aa.mailcode = bb.mailcode')
            ->leftJoin('App\Entity\MerchantConfig', 'cc', 'WITH', 'bb.takeorderby = cc.takeorderby')
            ->where('aa.catId is null AND cc.merType IN (\'holding\', \'afa\') and DATE(aa.systemReturnDate) between \'2019-11-01\' and \'2019-11-30\'')
            ->getQuery()
            ->getResult();
    }

}
