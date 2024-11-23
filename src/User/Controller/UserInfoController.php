<?php

namespace App\User\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;
use App\ApiDTO\Request\Base\EmptyRequest;
use App\User\Controller\Action\UploadAvatarAction;
use App\User\DTO\Request\Me\RequestEditMeGeoInfoDTO;
use App\User\DTO\Request\Me\RequestUploadAvatarDTO;
use App\User\DTO\Request\Me\RequestEditMeInfoDTO;
use App\User\DTO\Response\Me\ResponseMeInfoDTO;
use App\User\DTO\Response\Me\ResponseMeMainInfoDTO;
use App\User\Processor\Me\ApproveUserProcessor;
use App\User\Processor\Me\DeleteUserProcessor;
use App\User\Processor\Me\GeoInfoProcessor;
use App\User\Processor\Me\MainInfoProcessor;
use App\User\Processor\Me\UploadAvatarProcessor;
use App\User\Provider\MeInfoProvider;
use App\User\Provider\MeMainInfoProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

#[ApiResource(
    shortName: 'User Info',
    operations: [
        new Get(
            uriTemplate: '/user/info',
            openapi: new Operation(
                summary: 'Info about the current user',
                description: 'Info about the current user',
            ),
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            output: ResponseMeInfoDTO::class,
            provider: MeInfoProvider::class,
        ),
        new Get(
            uriTemplate: '/user/info/main',
            openapi: new Operation(
                summary: 'Full info about the current user',
                description: 'Full info about the current user',
            ),
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            output: ResponseMeMainInfoDTO::class,
            provider: MeMainInfoProvider::class,
        ),
        new Put(
            uriTemplate: '/user/info/main',
            openapi: new Operation(
                summary: 'Edit info for the current user',
                description: 'Edit info for the current user',
            ),
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            input: RequestEditMeInfoDTO::class,
            output: ResponseMeMainInfoDTO::class,
            processor: MainInfoProcessor::class,
        ),
        new Put(
            uriTemplate: '/user/info/geo',
            openapi: new Operation(
                summary: 'Set geo info for the current user',
                description: 'Set geo info for the current user',
            ),
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            input: RequestEditMeGeoInfoDTO::class,
            output: ResponseMeMainInfoDTO::class,
            processor: GeoInfoProcessor::class,
        ),
        new Post(
            uriTemplate: '/user/info/avatar',
            controller: UploadAvatarAction::class,
            openapi: new Operation(
                summary: 'Upload avatar for the current user',
                description: 'Upload avatar for the current user',
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
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            input: RequestUploadAvatarDTO::class,
            output: ResponseMeMainInfoDTO::class,
            deserialize: false,
            processor: UploadAvatarProcessor::class,
        ),
        new Delete(
            uriTemplate: '/user',
            status: Response::HTTP_ACCEPTED,
            openapi: new Operation(
                responses: [
                    Response::HTTP_ACCEPTED            => new OpenApiResponse('Accepted'),
                    Response::HTTP_NOT_FOUND           => new OpenApiResponse('Failed to get user'),
                    Response::HTTP_CONFLICT            => new OpenApiResponse('User already deleted'),
                    Response::HTTP_SERVICE_UNAVAILABLE => new OpenApiResponse('Failed to delete user'),
                ],
                summary: 'Delete current user',
                description: 'Delete current user',
            ),
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            input: EmptyRequest::class,
            processor: DeleteUserProcessor::class,
        ),
    ],
)]
class UserInfoController extends AbstractController
{
}

