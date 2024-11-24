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
            switch ($type) {
                case Marker::TYPE_GREENPOINT:
                    $criteria['isGreenPoint'] = true;
                break;
                case Marker::TYPE_CLOTH:
                    $criteria['isCloth'] = true;
                    break;
                case Marker::TYPE_BATTERY:
                    $criteria['isBattery'] = true;
                    break;
                case Marker::TYPE_ELECTRONIC:
                    $criteria['isElectronic'] = true;
                    break;
                case Marker::TYPE_GLASS:
                    $criteria['isGlass'] = true;
                    break;
                case Marker::TYPE_PLASTIC:
                    $criteria['isPlastic'] = true;
                    break;
                case Marker::TYPE_PAPER:
                    $criteria['isPaper'] = true;
                    break;
            }
            //$criteria['type'] = $type;
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