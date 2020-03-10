<?php

namespace App\Repository;

use App\Entity\PostinfoZipcodes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PostinfoZipcodes|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostinfoZipcodes|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostinfoZipcodes[]    findAll()
 * @method PostinfoZipcodes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostInfoZipcodesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PostinfoZipcodes::class);
    }

    public function getDhlSlaByDistrictCode($zipCode)
    {
        return $this->createQueryBuilder('p')
            ->select('p.dhlSla')
            ->andWhere('p.districtCode = :districtCode')
            ->setParameter('districtCode', $zipCode)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
    }
}
