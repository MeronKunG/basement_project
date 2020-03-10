<?php

namespace App\Repository;

use App\Entity\ParcelTempCapture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ParcelTempCapture|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParcelTempCapture|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParcelTempCapture[]    findAll()
 * @method ParcelTempCapture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParcelTempCaptureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ParcelTempCapture::class);
    }

    public function getImgUrlByByMailCode($mailCode)
    {
        return $this->createQueryBuilder('m')
            ->select('m.imageurl')
            ->andWhere('m.consignmentno = :consignmentno')
            ->setParameter('consignmentno', $mailCode)
            ->getQuery()
            ->getResult()
            ;
    }
}
