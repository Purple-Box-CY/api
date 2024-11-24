<?php

namespace App\Processor\Marker;

use App\ApiDTO\Request\Marker\RequestCreateMarker;
use App\ApiDTO\Response\Marker\ResponseMarkerInfo;
use App\Service\Infrastructure\LogService;
use App\Service\MarkerService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Exception\Http\BadRequest\BadFormatRequestException;
use App\Exception\Http\BadRequest\OperationBadRequestException;
use App\Exception\Http\RunTimeError\RunTimeHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CreateMarkerProcessor implements ProcessorInterface
{
    public function __construct(
        private MarkerService $markerService,
        private LogService    $logger,
    ) {
    }

    /**
     * @param RequestCreateMarker $data
     */
    public function process(
        $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): ResponseMarkerInfo|JsonResponse {
        if (!($data instanceof RequestCreateMarker)) {
            throw new BadFormatRequestException('Bad format input request');
        }

        if (!($operation instanceof Post)) {
            throw new OperationBadRequestException('Incorrect request type');
        }

        try {
            $marker = $this->markerService->createMarker(
                latitude: $data->latitude,
                longitude: $data->longitude,
                type: $data->type,
                description: $data->description,
            );
        } catch (\Throwable $e) {
            $this->logger->error('Failed to create Marker',
                [
                    'error' => $e->getMessage(),
                ]);
            throw new RunTimeHttpException('Failed to create Marker');
        }

        return new JsonResponse(
            data: ResponseMarkerInfo::create($marker),
            status: Response::HTTP_CREATED,
        );
    }
}