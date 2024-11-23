<?php

namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use App\ApiDTO\Response\Event\EventDTO;
use App\Provider\Event\EventProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[ApiResource(
    shortName: 'Events',
    operations: [
        new Get(
            uriTemplate: '/events',
            openapi: new Operation(
                summary: 'SSE Events',
                description: 'SSE Events',
            ),
            output: EventDTO::class,
            provider: EventProvider::class,
        ),
    ],
)]
class EventsController extends AbstractController
{
}