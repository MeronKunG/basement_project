<?php

namespace App\Repository;

use App\Entity\MerchantConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MerchantConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method MerchantConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method MerchantConfig[]    findAll()
 * @method MerchantConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MerchantConfigRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MerchantConfig::class);
    }

    public function getMerchantNameAndMerTypeByByTakeOrderBy($takeOrderBy)
    {
        return $this->createQueryBuilder('m')
            ->select('m.merchantname, m.merType')
            ->andWhere('m.takeorderby = :takeorderby')
            ->setParameter('takeorderby', $takeOrderBy)
            ->getQuery()
            ->getResult()
            ;
    }
    public function getMerchantNameByByTakeOrderByArray($takeOrderBy)
    {
        return $this->createQueryBuilder('m')
            ->select('m.takeorderby, m.merchantname')
            ->andWhere('m.takeorderby IN (:takeorderby)')
            ->setParameter('takeorderby', $takeOrderBy)
            ->getQuery()
            ->getResult()
            ;
    }
}
