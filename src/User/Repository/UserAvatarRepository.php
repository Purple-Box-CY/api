<?php

namespace App\User\Repository;

use App\User\Entity\UserAvatar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserAvatar>
 *
 * @method UserAvatar|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAvatar|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAvatar[]    findAll()
 * @method UserAvatar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAvatarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAvatar::class);
    }

}