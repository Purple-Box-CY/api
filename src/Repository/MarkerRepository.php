<?php

namespace App\Repository;

use App\Entity\Marker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Marker>
 *
 * @method Marker|null find($id, $lockMode = null, $lockVersion = null)
 * @method Marker|null findOneBy(array $criteria, array $orderBy = null)
 * @method Marker[]    findAll()
 * @method Marker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Marker::class);
    }

    public function save(Marker $object): Marker
    {
        $object->setUpdatedAt(new \DateTime('now'));

        $this->_em->persist($object);
        $this->_em->flush();

        return $object;
    }

}
