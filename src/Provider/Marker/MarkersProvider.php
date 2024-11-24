<?php

namespace App\Provider\Marker;

use App\ApiDTO\Response\Marker\MarkerLocation;
use App\ApiDTO\Response\Marker\ResponseMarkerList;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiDTO\Response\Marker\ResponseMarkerShort;
use App\Entity\Marker;
use App\Exception\Http\NotFound\ObjectNotFoundHttpException;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\MarkerService;
use App\Service\Utility\MomentHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class MarkersProvider implements ProviderInterface
{
    public function __construct(
        private readonly MarkerService $markerService,
        private readonly RedisService  $redisService,
        private readonly RequestStack  $requestStack,
    ) {
    }

    public function provide(
        Operation $operation,
        array     $uriVariables = [],
        array     $context = []
    ): ResponseMarkerList|JsonResponse {
        if ($operation instanceof CollectionOperationInterface) {
            throw new \RuntimeException('Not supported.');
        }

        $request = $this->requestStack->getCurrentRequest();
        $type = $request->get('type');
        if ($type && !in_array($type, Marker::AVAILABLE_TYPES)) {
            $type = null;
        }

        $redisKey = sprintf(RedisKeys::KEY_MARKERS, $type ?: 'all');
        $markersItems = $this->redisService->getObjects($redisKey, false);
        if ($markersItems) {
            return new JsonResponse(
                data: ResponseMarkerList::create(
                    markers: $markersItems,
                ),
            );
        }

        try {
            $markers = $this->markerService->getMarkers(
                type: $type,
            );
        } catch (\Throwable $e) {
            throw new ObjectNotFoundHttpException($e->getMessage() ?? 'Failed to get markers');
        }

        $markersItems = [];
        foreach ($markers as $marker) {
            $markersItems[] = new ResponseMarkerShort(
                uid: $marker->getUid(),
                type: $marker->getType(),
                name: $marker->getName(),
                description: $marker->getShortDescription(),
                imageUrl: $marker->getImageUrl(),
                location: new MarkerLocation($marker->getLat(), $marker->getLng()),
            );
        }

        $this->redisService->setObjects($redisKey, $markersItems, MomentHelper::SECONDS_WEEK, false);

        return ResponseMarkerList::create(
            markers: $markersItems,
        );
    }
}
