<?php

namespace App\Repository;

use App\Entity\TestAllParcel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TestAllParcel|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestAllParcel|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestAllParcel[]    findAll()
 * @method TestAllParcel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestAllParcelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestAllParcel::class);
    }

    public function getCountDataByDate($date, $ffm)
    {
        return $this->createQueryBuilder('t')
            ->select('t.mailcode, t.shopname, t.ffmOwner, t.ffm, t.statuscode')
            ->where('t.ymOd = :date AND t.ffm = :ffm AND t.statuscode <> \'101\'')
            ->setParameter('date', $date)
            ->setParameter('ffm', $ffm)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getLastDataByDate($date, $ffm)
    {
        return $this->createQueryBuilder('aa')
            ->select('aa.mailcode, bb.haul, aa.statuscode')
            ->leftJoin('App\Entity\PostZipDhlN', 'bb', 'WITH', 'aa.zipcode = bb.zipcode')
            ->where('aa.ymOd = :date AND aa.ffm = :ffm AND aa.statuscode <> \'101\'')
            ->setParameter('date', $date)
            ->setParameter('ffm', $ffm)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getDataMailCodeAndRecipientPhone($senderId, $startDate, $endDate)
    {
        return $this->createQueryBuilder('t')
            ->select('g.statusnameTh, mb.takeorderby, t.mailcode, mb.ordername, t.recipientPhone AS orderphoneno, DATE(mb.orderdate) as orderdate, mb.parcelMemberId, mbd.transporterId, mb.paymentInvoice')
            ->leftjoin('App\Entity\MerchantBillingDelivery', 'mbd', 'WITH','t.mailcode = mbd.mailcode')
            ->leftjoin('App\Entity\MerchantBilling', 'mb', 'WITH',
                'mb.takeorderby = mbd.takeorderby AND mb.paymentInvoice = mbd.paymentInvoice')
            ->leftjoin('App\Entity\GlobalOrderstatus', 'g', 'WITH', 't.statuscode = g.statuscode')
            ->where('t.senderId = :senderId AND t.sendmaildate BETWEEN :startDate AND :endDate')
            ->andWhere('t.statuscode = \'106\'')
            ->setParameter('senderId', $senderId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getDateByDate($date)
    {
        return $this->createQueryBuilder('t')
            ->select('DISTINCT(t.sendmaildate)')
            ->where('t.ymOd = :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getDateNameByDateAndTransportType($date, $ffm)
    {
        return $this->createQueryBuilder('t')
            ->select('t.shopname, t.ffmOwner, t.mailcode, t.statuscode, t.transportType')
            ->where('t.ymOd = :date AND t.ffm = :ffm AND t.statuscode = \'106\'')
            ->andWhere('t.transportType IN (\'cod\', \'normal\')')
            ->setParameter('date', $date)
            ->setParameter('ffm', $ffm)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getDateNameByDateAndShopName($date, $ffm, $name)
    {
        return $this->createQueryBuilder('t')
            ->select('t.shopname, t.mailcode, t.statuscode, t.transportType')
            ->where('t.ymOd = :date AND t.shopname = :shopname AND t.ffm = :ffm AND t.statuscode IN (\'104\',\'105\',\'106\')')
            ->setParameter('shopname', $name)
            ->setParameter('date', $date)
            ->setParameter('ffm', $ffm)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getDateNameByDateAndOwnerName($date, $ffm, $name)
    {
        return $this->createQueryBuilder('t')
            ->select('t.ffmOwner, t.mailcode, t.statuscode, t.transportType')
            ->where('t.ymOd = :date AND t.ffmOwner = :ffmOwner AND t.ffm = :ffm AND t.statuscode IN (\'104\',\'105\',\'106\')')
            ->setParameter('ffmOwner', $name)
            ->setParameter('date', $date)
            ->setParameter('ffm', $ffm)
            ->getQuery()
            ->getResult()
            ;
    }

}
