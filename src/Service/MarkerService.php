<?php

namespace App\Service;

use App\Entity\Marker;
use App\Repository\MarkerRepository;
use Symfony\Component\Uid\Ulid;

class MarkerService
{
    public function __construct(
        private MarkerRepository $markerRepository,
    ) {
    }

    /**
     * @return Marker[]
     */
    public function getMarkers(string $type = null): array
    {
        $criteria = [
            'status' => Marker::STATUS_ACTIVE,
        ];

        if ($type) {
            $criteria['type'] = $type;
        }

        return $this->markerRepository->findBy($criteria);
    }

    public function getMarkerByUid(string $uid): ?Marker
    {
        return $this->markerRepository->findOneBy([
            'uid' => new Ulid($uid),
        ]);
    }
}