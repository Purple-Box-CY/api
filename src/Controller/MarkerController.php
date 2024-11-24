<?php

namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use App\ApiDTO\Response\Article\ArticleResponse;
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
            output: ArticleResponse::class,
            provider: MarkersProvider::class,
        ),
    ],
)]
class MarkerController extends AbstractController
{
}
