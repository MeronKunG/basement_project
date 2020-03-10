<?php

namespace App\Repository;

use App\Entity\GlobalAuthen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobalAuthen|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalAuthen|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalAuthen[]    findAll()
 * @method GlobalAuthen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalAuthenRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobalAuthen::class);
    }
}
