<?php

namespace App\Repository;

use App\Entity\ParcelMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ParcelMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParcelMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParcelMember[]    findAll()
 * @method ParcelMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParcelMemberRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ParcelMember::class);
    }

    public function getFirstNameAndLastNameAndPhoneByMemberId($memberId)
    {
        return $this->createQueryBuilder('p')
            ->select('p.firstname, p.lastname, p.phoneregis')
            ->andWhere('p.memberId = :memberId')
            ->setParameter('memberId', $memberId)
            ->getQuery()
            ->getResult()
            ;
    }
}
