<?php

namespace App\Service\Infrastructure;

use Doctrine\ORM\EntityManagerInterface;

class EntityService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function getReference(string $class, int $id): mixed
    {
        return $this->em->getReference($class, $id);
    }

}
