<?php

namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\ApiDTO\Request\Camera\RequestUploadImageDTO;
use App\ApiDTO\Response\Camera\ResponseUploadImageDTO;
use App\Controller\Action\CameraUploadAction;
use App\Processor\Camera\UploadImageProcessor;
use App\Processor\Camera\UploadImageTrashProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[ApiResource(
    shortName: 'Camera',
    operations: [
        new Post(
            uriTemplate: '/camera/box',
            controller: CameraUploadAction::class,
            openapi: new Operation(
                summary: 'Upload image for recognition',
                description: 'Upload image for recognition',
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'file' => [
                                        'type'   => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ])
                )
            ),
            input: RequestUploadImageDTO::class,
            output: ResponseUploadImageDTO::class,
            deserialize: false,
            processor: UploadImageProcessor::class,
        ),
        new Post(
            uriTemplate: '/camera/trash',
            controller: CameraUploadAction::class,
            openapi: new Operation(
                summary: 'Upload image for recognition',
                description: 'Upload image for recognition',
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'file' => [
                                        'type'   => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ])
                )
            ),
            input: RequestUploadImageDTO::class,
            output: ResponseUploadImageDTO::class,
            deserialize: false,
            processor: UploadImageTrashProcessor::class,
        ),
    ],
)]
class CameraController extends AbstractController
{
}
