<?php

namespace App\Provider\Marker;

use App\ApiDTO\Response\Marker\MarkerLocation;
use App\ApiDTO\Response\Marker\ResponseMarkerInfo;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Exception\Http\NotFound\ObjectNotFoundHttpException;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\MarkerService;
use App\Service\Utility\FormatHelper;
use App\Service\Utility\MomentHelper;
use Symfony\Component\HttpFoundation\JsonResponse;

class MarkerInfoProvider implements ProviderInterface
{
    public function __construct(
        private readonly MarkerService $markerService,
        private readonly RedisService  $redisService,
    ) {
    }

    public function provide(
        Operation $operation,
        array     $uriVariables = [],
        array     $context = []
    ): ResponseMarkerInfo|JsonResponse {
        if ($operation instanceof CollectionOperationInterface) {
            throw new \RuntimeException('Not supported.');
        }

        $uid = $uriVariables['uid'] ?? null;
        if (!FormatHelper::isValidUid($uid)) {
            throw new ObjectNotFoundHttpException('Marker not found.');
        }

        $redisKey = sprintf(RedisKeys::KEY_MARKER, $uid);
        $markerItemResponse = $this->redisService->getObject($redisKey, false);
        if ($markerItemResponse) {
            return new JsonResponse(
                data: $markerItemResponse,
            );
        }

        try {
            $marker = $this->markerService->getMarkerByUid($uid);
        } catch (\Throwable $e) {
            throw new ObjectNotFoundHttpException($e->getMessage() ?? 'Failed to get marker');
        }

        if (!$marker) {
            throw new ObjectNotFoundHttpException('Marker not found.');
        }

        $markerItemResponse = ResponseMarkerInfo::create($marker);

        $this->redisService->setObject($redisKey, $markerItemResponse, MomentHelper::SECONDS_WEEK, false);

        return new JsonResponse(
            data: $markerItemResponse,
        );
    }
}
