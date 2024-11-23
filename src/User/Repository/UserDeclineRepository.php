<?php

namespace App\User\Repository;

use App\User\Entity\UserDecline;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserDecline>
 *
 * @method UserDecline|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserDecline|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserDecline[]    findAll()
 * @method UserDecline[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDeclineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDecline::class);
    }

}