<?php

namespace App\Repository;

use App\Entity\MerchantBillingDelivery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MerchantBillingDelivery|null find($id, $lockMode = null, $lockVersion = null)
 * @method MerchantBillingDelivery|null findOneBy(array $criteria, array $orderBy = null)
 * @method MerchantBillingDelivery[]    findAll()
 * @method MerchantBillingDelivery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MerchantBillingDeliveryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MerchantBillingDelivery::class);
    }

    // /**
    //  * @return MerchantBillingDelivery[] Returns an array of MerchantBillingDelivery objects
    //  */

    public function getInvoiceAndTakeOrderByByEmailCode($mailcode)
    {
        return $this->createQueryBuilder('m')
            ->select('m.mailcode, m.takeorderby, m.paymentInvoice, m.transporterId, m.codPrice, m.expenseDiscount, m.sendmaildate, m.transactiondate')
            ->andWhere('m.mailcode = :val')
            ->setParameter('val', $mailcode)
            ->getQuery()
            ->getResult();
    }

    public function getInvoiceAndTakeOrderByByTakeOrderByAndInvoice($takeOrderBy, $invoice)
    {
        return $this->createQueryBuilder('m')
            ->select('m.mailcode, m.takeorderby, m.paymentInvoice, m.transporterId, m.codPrice, m.expenseDiscount, m.sendmaildate, m.transactiondate')
            ->where('m.takeorderby = :takeorderby AND m.paymentInvoice = :paymentInvoice')
            ->setParameter('takeorderby', $takeOrderBy)
            ->setParameter('paymentInvoice', $invoice)
            ->getQuery()
            ->getResult();
    }

    public function getInvoiceAndTakeOrderByByPaymentInvoice($invoice)
    {
        return $this->createQueryBuilder('m')
            ->select('m.mailcode, m.takeorderby, m.paymentInvoice, m.transporterId, m.codPrice, m.expenseDiscount, m.sendmaildate, m.sendmaildate, m.transactiondate')
            ->andWhere('m.paymentInvoice = :val')
            ->setParameter('val', $invoice)
            ->getQuery()
            ->getResult();
    }

    public function getMailCodeBySessionAdjustmentNull($sendMailDate, $merType, $billing, $takeOrderBy)
    {
        return $this->createQueryBuilder('mbd')
            ->select('mbd.mailcode, zip.priority, mb.ordertransport, mc.takeorderby, mc.merchantname')
            ->leftJoin('App\Entity\MerchantBilling', 'mb', 'WITH',
                'mbd.takeorderby = mb.takeorderby AND mbd.paymentInvoice = mb.paymentInvoice AND mb.orderstatus = \'104\'')
            ->leftJoin('App\Entity\PostZipDhlN', 'zip', 'WITH', 'mb.zipcode = zip.zipcode')
            ->leftJoin('App\Entity\TrackingLog', 'log', 'WITH', 'mbd.mailcode = log.mailcode')
            ->leftJoin('App\Entity\MerchantConfig', 'mc', 'WITH', 'mbd.takeorderby = mc.takeorderby')
            ->where('DATE_FORMAT(mbd.sendmaildate, \'%Y-%m-%d\') = :sendMailDate')
            ->andWhere('mc.merType IN (:merType)')
            ->andWhere('mb.ordertransport IN (:billing)')
            ->andWhere('mb.orderstatus = \'104\' AND log.mailcode is null')
            ->andWhere('mc.takeorderby IN (:takeorderby)')
            ->setParameter('sendMailDate', $sendMailDate)
            ->setParameter('merType', $merType)
            ->setParameter('billing', $billing)
            ->setParameter('takeorderby', $takeOrderBy)
            ->getQuery()
            ->getResult();
    }

    public function getMailCodeBySessionAdjustmentNotNull($sendMailDate, $merType, $billing, $takeOrderBy)
    {
        return $this->createQueryBuilder('mbd')
            ->select('mbd.mailcode, zip.priority, mb.ordertransport, mc.takeorderby, mc.merchantname')
            ->leftJoin('App\Entity\MerchantBilling', 'mb', 'WITH',
                'mbd.takeorderby = mb.takeorderby AND mbd.paymentInvoice = mb.paymentInvoice AND mb.orderstatus = \'104\'')
            ->leftJoin('App\Entity\PostZipDhlN', 'zip', 'WITH', 'mb.zipcode = zip.zipcode')
//            ->leftJoin('App\Entity\TrackingLog', 'log', 'WITH', 'mbd.mailcode = log.mailcode')
            ->leftJoin('App\Entity\MerchantConfig', 'mc', 'WITH', 'mbd.takeorderby = mc.takeorderby')
            ->leftJoin('App\Entity\TrackingLogLatest', 'logl', 'WITH', 'mbd.mailcode = logl.mailcode')
            ->where('DATE_FORMAT(mbd.sendmaildate, \'%Y-%m-%d\') = :sendMailDate')
            ->andWhere('mc.merType IN (:merType)')
            ->andWhere('mb.ordertransport IN (:billing)')
            ->andWhere('mb.orderstatus = \'104\' AND logl.deliveryStatus = \'Delivery Process\'')
            ->andWhere('mc.takeorderby IN (:takeorderby)')
            ->andWhere('logl.updateDate < CURRENT_DATE()')
            ->setParameter('sendMailDate', $sendMailDate)
            ->setParameter('merType', $merType)
            ->setParameter('billing', $billing)
            ->setParameter('takeorderby', $takeOrderBy)
//            ->groupBy('mbd.mailcode')
//            ->having('max(logl.tstamp) < CURRENT_DATE()')
            ->getQuery()
            ->getResult();
    }

    public function getMailCodeBySessionNull($sendMailDate, $merType, $billing)
    {
        return $this->createQueryBuilder('mbd')
            ->select('mbd.mailcode, zip.priority, mb.ordertransport, mc.takeorderby')
            ->leftJoin('App\Entity\MerchantBilling', 'mb', 'WITH',
                'mbd.takeorderby = mb.takeorderby AND mbd.paymentInvoice = mb.paymentInvoice AND mb.orderstatus = \'104\'')
            ->leftJoin('App\Entity\PostZipDhlN', 'zip', 'WITH', 'mb.zipcode = zip.zipcode')
            ->leftJoin('App\Entity\TrackingLog', 'log', 'WITH', 'mbd.mailcode = log.mailcode')
            ->leftJoin('App\Entity\MerchantConfig', 'mc', 'WITH', 'mbd.takeorderby = mc.takeorderby')
            ->where('DATE_FORMAT(mbd.sendmaildate, \'%Y-%m-%d\') = :sendMailDate')
            ->andWhere('mc.merType IN (:merType)')
            ->andWhere('mb.ordertransport IN (:billing)')
            ->andWhere('mb.orderstatus = \'104\' AND log.mailcode is null')
            ->setParameter('sendMailDate', $sendMailDate)
            ->setParameter('merType', $merType)
            ->setParameter('billing', $billing)
            ->getQuery()
            ->getResult();
    }

    public function getMailCodeBySessionNotNull($sendMailDate, $merType, $billing)
    {
        return $this->createQueryBuilder('mbd')
            ->select('mbd.mailcode, zip.priority, mb.ordertransport, mc.takeorderby')
            ->leftJoin('App\Entity\MerchantBilling', 'mb', 'WITH',
                'mbd.takeorderby = mb.takeorderby AND mbd.paymentInvoice = mb.paymentInvoice AND mb.orderstatus = \'104\'')
            ->leftJoin('App\Entity\PostZipDhlN', 'zip', 'WITH', 'mb.zipcode = zip.zipcode')
//            ->leftJoin('App\Entity\TrackingLog', 'log', 'WITH', 'mbd.mailcode = log.mailcode')
            ->leftJoin('App\Entity\MerchantConfig', 'mc', 'WITH', 'mbd.takeorderby = mc.takeorderby')
            ->leftJoin('App\Entity\TrackingLogLatest', 'logl', 'WITH', 'mbd.mailcode = logl.mailcode')
            ->where('DATE_FORMAT(mbd.sendmaildate, \'%Y-%m-%d\') = :sendMailDate')
            ->andWhere('mc.merType IN (:merType)')
            ->andWhere('mb.ordertransport IN (:billing)')
            ->andWhere('mb.orderstatus = \'104\' AND logl.deliveryStatus = \'Delivery Process\'')
            ->andWhere('logl.updateDate < CURRENT_DATE()')
            ->setParameter('sendMailDate', $sendMailDate)
            ->setParameter('merType', $merType)
            ->setParameter('billing', $billing)
//            ->groupBy('mbd.mailcode')
//            ->having('max(logl.tstamp) < CURRENT_DATE()')
            ->getQuery()
            ->getResult();
    }

    public function getBillingDetailByByMailCode($mailCode)
    {
        return $this->createQueryBuilder('mbd')
            ->select('md.globalProductid AS parentId, md.productorder AS totalQty, md.productprice AS unitPrice, md.productname AS productname, c.catName AS catName, mbd.sendmaildate')
            ->leftJoin('App\Entity\MerchantBillingDetail', 'md', 'WITH', 'mbd.takeorderby = md.takeorderby AND mbd.paymentInvoice = md.paymentInvoice')
            ->leftJoin('App\Entity\TestCsReturnCollect', 'r', 'WITH', 'mbd.mailcode = r.mailcode')
            ->leftJoin('App\Entity\TestCsReturnCat', 'c', 'WITH', 'r.catId = c.catId')
            ->where('mbd.mailcode = :mailCode')
            ->setParameter('mailCode', $mailCode)
            ->getQuery()
            ->getResult();
    }

    public function getShopNameByTakeOrderBy($mailCode)
    {
        return $this->createQueryBuilder('mbd')
            ->select('mbd.mailcode, mc.merchantname')
            ->leftJoin('App\Entity\MerchantConfig', 'mc', 'WITH', 'mbd.takeorderby = mc.takeorderby')
            ->where('mbd.mailcode =:mailCode')
            ->setParameter('mailCode', $mailCode)
            ->getQuery()
            ->getResult();
    }
}
