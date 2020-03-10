<?php

namespace App\Repository;

use App\Entity\MerchantBilling;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MerchantBilling|null find($id, $lockMode = null, $lockVersion = null)
 * @method MerchantBilling|null findOneBy(array $criteria, array $orderBy = null)
 * @method MerchantBilling[]    findAll()
 * @method MerchantBilling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MerchantBillingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MerchantBilling::class);
    }

    public function getOrderNameAndOrderPhoneNoByInvoiceAndTakeOrderBy($takeOrderBy, $paymentInvoice)
    {
        return $this->createQueryBuilder('mb')
            ->select('mb.ordername, mb.ordertransport, mb.orderphoneno, CONCAT(mb.orderaddr, \' | \', mb.district, \' | \', mb.amphur, \' | \', mb.province, \' | \', mb.zipcode) AS address, mb.transportprice, mb.orderstatus, mb.parcelMemberId, mb.ordershortnote, mb.adminid, mb.districtcode, mb.orderdate')
            ->where('mb.takeorderby = :takeorderby')
            ->andWhere('mb.paymentInvoice = :paymentInvoice')
            ->setParameter('takeorderby', $takeOrderBy)
            ->setParameter('paymentInvoice', $paymentInvoice)
            ->getQuery()
            ->getResult();
    }

    public function getMailCodeAndDataBySearch($search)
    {
        return $this->createQueryBuilder('mb')
            ->select('g.statusnameTh, mb.takeorderby, mbd.mailcode, mb.ordername, mb.orderphoneno, DATE(mb.orderdate) as orderdate, mb.parcelMemberId, mbd.transporterId, mb.paymentInvoice')
            ->leftjoin('App\Entity\MerchantBillingDelivery', 'mbd', 'WITH',
                'mb.takeorderby = mbd.takeorderby AND mb.paymentInvoice = mbd.paymentInvoice')
            ->leftjoin('App\Entity\GlobalOrderstatus', 'g', 'WITH', 'mb.orderstatus = g.statuscode')
            ->where('mb.orderphoneno = :search')
            ->setParameter('search', $search)
            ->getQuery()
            ->getResult();
    }

    public function getMailCodeAndDataBySearchName($search)
    {
        return $this->createQueryBuilder('mb')
            ->select('g.statusnameTh, mb.takeorderby, mbd.mailcode, mb.ordername, mb.orderphoneno, DATE(mb.orderdate) as orderdate, mb.parcelMemberId, mbd.transporterId, mb.paymentInvoice')
            ->leftjoin('App\Entity\MerchantBillingDelivery', 'mbd', 'WITH',
                'mb.takeorderby = mbd.takeorderby AND mb.paymentInvoice = mbd.paymentInvoice')
            ->leftjoin('App\Entity\GlobalOrderstatus', 'g', 'WITH', 'mb.orderstatus = g.statuscode')
            ->where('mb.ordername LIKE :search')
            ->setParameter('search', $search. '%')
            ->getQuery()
            ->getResult();
    }
}
