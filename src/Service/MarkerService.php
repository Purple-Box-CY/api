<?php

namespace App\Service;

use App\Entity\Marker;
use App\Repository\MarkerRepository;
use App\Service\Infrastructure\LogService;
use App\Service\Infrastructure\RedisService;

class MarkerService
{

    public function __construct(
        private RedisService     $redisService,
        private MarkerRepository $markerRepository,
        private LogService       $logger,
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
}