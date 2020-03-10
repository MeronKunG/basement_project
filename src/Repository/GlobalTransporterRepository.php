<?php

namespace App\Repository;

use App\Entity\GlobalTransporter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobalTransporter|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalTransporter|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalTransporter[]    findAll()
 * @method GlobalTransporter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalTransporterRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobalTransporter::class);
    }

    public function getTransporterNameByTransportId($id)
    {
        return $this->createQueryBuilder('t')
            ->select('t.transporterName')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
            ;
    }
}
