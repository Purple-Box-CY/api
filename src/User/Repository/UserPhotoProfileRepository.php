<?php

namespace App\User\Repository;

use App\User\Entity\UserPhotoProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPhotoProfile>
 *
 * @method UserPhotoProfile|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPhotoProfile|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPhotoProfile[]    findAll()
 * @method UserPhotoProfile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPhotoProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPhotoProfile::class);
    }

}