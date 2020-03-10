<?php

namespace App\Repository;

use App\Entity\PcComApproved;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PcComApproved|null find($id, $lockMode = null, $lockVersion = null)
 * @method PcComApproved|null findOneBy(array $criteria, array $orderBy = null)
 * @method PcComApproved[]    findAll()
 * @method PcComApproved[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PcComApprovedRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PcComApproved::class);
    }

    public function getTransferAmtByTakeOrderBy($takeorderby, $invoice)
    {
        return $this->createQueryBuilder('p')
            ->select('p.transferAmt, p.tfd')
            ->where('p.takeorderby =:takeorderby AND p.invoice =:invoice')
            ->setParameter('takeorderby', $takeorderby)
            ->setParameter('invoice', $invoice)
            ->getQuery()
            ->getResult()
            ;
    }
}
