<?php

namespace App\Repository;

use App\Entity\CodReturn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CodReturn|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodReturn|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodReturn[]    findAll()
 * @method CodReturn[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodReturnRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CodReturn::class);
    }

    public function getScanDataAndImgUrlByMailCode($mailcode)
    {
        return $this->createQueryBuilder('c')
            ->select('c.scanDate, c.imgUrl')
            ->andWhere('c.mailcode = :mailCode')
            ->setParameter('mailCode', $mailcode)
            ->getQuery()
            ->getResult();
    }

}
