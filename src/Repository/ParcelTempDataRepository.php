<?php

namespace App\Repository;

use App\Entity\ParcelTempData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ParcelTempData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParcelTempData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParcelTempData[]    findAll()
 * @method ParcelTempData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParcelTempDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ParcelTempData::class);
    }

    public function getDataByConsignmentNo($con)
    {
        return $this->createQueryBuilder('aa')
            ->select('aa.operator, bb.operatorName, bb.groupName')
            ->leftJoin('App\Entity\TestKeyinGroup', 'bb', 'WITH', 'aa.operator = bb.operator')
            ->where('aa.consignmentno = :consignmentno')
            ->setParameter('consignmentno', $con)
            ->getQuery()
            ->getResult()
            ;
    }
}
