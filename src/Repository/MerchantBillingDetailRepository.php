<?php

namespace App\Repository;

use App\Entity\MerchantBillingDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MerchantBillingDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method MerchantBillingDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method MerchantBillingDetail[]    findAll()
 * @method MerchantBillingDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MerchantBillingDetailRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MerchantBillingDetail::class);
    }

    public function getProductNameAndProductOrderByTakeOrderByAndInvoice($takeOrderBy, $paymentInvoice)
    {
        return $this->createQueryBuilder('md')
            ->select('md.productname, md.productorder')
            ->where('md.takeorderby = :takeorderby')
            ->andWhere('md.paymentInvoice = :paymentInvoice')
            ->setParameter('takeorderby', $takeOrderBy)
            ->setParameter('paymentInvoice', $paymentInvoice)
            ->getQuery()
            ->getResult()
            ;
    }
}
