<?php

namespace App\User\Repository;

use App\User\Entity\UserImageProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserImageProfile>
 *
 * @method UserImageProfile|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserImageProfile|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserImageProfile[]    findAll()
 * @method UserImageProfile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserImageProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserImageProfile::class);
    }

}