<?php

namespace App\Repository;

use App\Entity\TestQuicklinkDetection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TestQuicklinkDetection|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestQuicklinkDetection|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestQuicklinkDetection[]    findAll()
 * @method TestQuicklinkDetection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestQuickLinkDetectionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestQuicklinkDetection::class);
    }

    public function getDataByDataIsNull()
    {
        return $this->createQueryBuilder('q')
            ->select('q.mailcode, q.qlZipcode, q.errorCode, q.keyZipcode, q.qlTransportType, q.keyTransportType, q.captureImage, q.captureDate, q.clearStatus, q.updatedBy')
            ->where('q.errorCode <> 0')
            ->getQuery()
            ->getResult();
    }

    public function getDataByDataIsNullBeAction($action)
    {
        return $this->createQueryBuilder('q')
            ->select('q.mailcode, q.qlZipcode, q.errorCode, q.keyZipcode, q.qlTransportType, q.keyTransportType, q.captureImage, q.captureDate, q.clearStatus, q.updatedBy')
            ->where('q.clearStatus =:status')
            ->setParameter('status', $action)
            ->getQuery()
            ->getResult();
    }

    public function getDataByDataIsNullBeActionOnZero($action)
    {
        return $this->createQueryBuilder('q')
            ->select('q.mailcode, q.qlZipcode, q.errorCode, q.keyZipcode, q.qlTransportType, q.keyTransportType, q.captureImage, q.captureDate, q.clearStatus, q.updatedBy')
            ->where('q.errorCode <> 0 AND q.clearStatus =:status')
            ->setParameter('status', $action)
            ->getQuery()
            ->getResult();
    }

    public function getDataBySearch($search)
    {
        return $this->createQueryBuilder('q')
            ->select('q.mailcode, q.qlZipcode, q.errorCode, q.keyZipcode, q.qlTransportType, q.keyTransportType, q.captureImage, q.captureDate, q.clearStatus, q.updatedBy')
            ->where('q.errorCode <> 0')
            ->andWhere('DATE(q.captureDate) = :search')
            ->setParameter('search', $search)
            ->getQuery()
            ->getResult();
    }
    public function getDataBySearch2($search, $action)
    {
        return $this->createQueryBuilder('q')
            ->select('cc.merchantname, q.mailcode, q.qlZipcode, q.errorCode, q.keyZipcode, q.qlTransportType, q.keyTransportType, q.captureImage, q.captureDate, q.clearStatus, q.updatedBy')
            ->leftJoin('App\Entity\MerchantBillingDelivery', 'bb' , 'WITH', 'q.mailcode = bb.mailcode')
            ->leftJoin('App\Entity\MerchantConfig', 'cc' , 'WITH', 'bb.takeorderby = cc.takeorderby')
            ->where('DATE(q.captureDate) = :search AND q.clearStatus =:status')
            ->setParameter('search', $search)
            ->setParameter('status', $action)
            ->getQuery()
            ->getResult();
    }

    public function getDataBySearch2OnZero($search, $action)
    {
        return $this->createQueryBuilder('q')
            ->select('cc.merchantname, q.mailcode, q.qlZipcode, q.errorCode, q.keyZipcode, q.qlTransportType, q.keyTransportType, q.captureImage, q.captureDate, q.clearStatus, q.updatedBy')
            ->leftJoin('App\Entity\MerchantBillingDelivery', 'bb' , 'WITH', 'q.mailcode = bb.mailcode')
            ->leftJoin('App\Entity\MerchantConfig', 'cc' , 'WITH', 'bb.takeorderby = cc.takeorderby')
            ->where('DATE(q.captureDate) = :search AND q.errorCode <> 0')
            ->andWhere('q.clearStatus =:status')
            ->setParameter('search', $search)
            ->setParameter('status', $action)
            ->getQuery()
            ->getResult();
    }
}
