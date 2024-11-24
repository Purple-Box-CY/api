<?php

namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;
use App\ApiDTO\Request\Marker\RequestCreateMarker;
use App\ApiDTO\Response\Marker\ResponseMarkerInfo;
use App\ApiDTO\Response\Marker\ResponseMarkerList;
use App\Processor\Marker\CreateMarkerProcessor;
use App\Provider\Marker\MarkerInfoProvider;
use App\Provider\Marker\MarkersProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

#[ApiResource(
    shortName: 'Markers',
    operations: [
        new Get(
            uriTemplate: '/markers',
            openapi: new Operation(
                summary: 'Markers',
                description: 'Markers',
            ),
            output: ResponseMarkerList::class,
            provider: MarkersProvider::class,
        ),
        new Post(
            uriTemplate: '/markers',
            status: Response::HTTP_CREATED,
            openapi: new Operation(
                responses: [
                    Response::HTTP_CREATED   => new OpenApiResponse('Success',),
                ],
                summary: 'Create marker',
                description: 'Create marker',
            ),
            //security: "is_granted('IS_AUTHENTICATED_FULLY')",
            input: RequestCreateMarker::class,
            output: ResponseMarkerInfo::class,
            processor: CreateMarkerProcessor::class,
        ),
        new Get(
            uriTemplate: '/markers/{uid}',
            openapi: new Operation(
                summary: 'Marker info',
                description: 'Marker info',
            ),
            output: ResponseMarkerInfo::class,
            provider: MarkerInfoProvider::class,
        ),
    ],
)]
class MarkerController extends AbstractController
{
}
