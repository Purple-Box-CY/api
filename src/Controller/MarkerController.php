<?php

namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use App\ApiDTO\Response\Marker\ResponseMarkerInfo;
use App\ApiDTO\Response\Marker\ResponseMarkerList;
use App\Provider\Marker\MarkerInfoProvider;
use App\Provider\Marker\MarkersProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
