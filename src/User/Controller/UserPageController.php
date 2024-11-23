<?php

namespace App\User\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use App\User\DTO\Response\ResponseUserInfoDTO;
use App\User\Provider\InfoProvider;
use App\User\Provider\ProfileProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[ApiResource(
    shortName: 'User Page',
    operations: [
        new Get(
            uriTemplate: '/user/{uid}',
            openapi: new Operation(
                summary: 'User info',
                description: 'User info',
            ),
            output: ResponseUserInfoDTO::class,
            provider: InfoProvider::class,
        ),
        new Get(
            uriTemplate: '/user/profile/{username}',
            openapi: new Operation(
                summary: 'User profile info',
                description: 'User profile info',
            ),
            output: ResponseUserInfoDTO::class,
            provider: ProfileProvider::class,
        ),
    ],
)]
class UserPageController extends AbstractController
{
}

