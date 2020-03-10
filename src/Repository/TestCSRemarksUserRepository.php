<?php

namespace App\Repository;

use App\Entity\TestCsRemarksUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method TestCsRemarksUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestCsRemarksUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestCsRemarksUser[]    findAll()
 * @method TestCsRemarksUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestCSRemarksUserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestCsRemarksUser::class);
    }

    public function getUserName($username)
    {
        return $this->createQueryBuilder('t')
            ->where('t.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @inheritDoc
     */
    public function loadUserByUsername($username)
    {
        // TODO: Implement loadUserByUsername() method.
    }
}
