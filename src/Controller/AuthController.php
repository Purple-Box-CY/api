<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Metadata\Get;
use App\ApiDTO\Response\Auth\HashResponse;
use App\Provider\Auth\AuthCmsProvider;
use App\Provider\Auth\AuthTokenInstagramProvider;
use App\Provider\Auth\TokenByTypeProvider;
use App\Provider\Auth\TokenProvider;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Provider\Auth\AuthGoogleProvider;
use App\ApiDTO\Response\Auth\TokenResponse;
use App\Provider\Auth\ConnectGoogleProvider;
use App\Provider\Auth\AuthGoogleTokenProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[ApiResource(
    shortName: 'Auth',
    operations: [
        new Get(
            uriTemplate: '/auth/token/{hash}',
            stateless: true,
            openapi: new Operation(
                summary: 'Get auth tokens',
                description: 'Get auth tokens',
                parameters: [
                    new Parameter(
                        name: 'hash',
                        in: 'path',
                        description: 'Get token by provided hash',
                        required: true,
                    ),
                ]
            ),
            output: TokenResponse::class,
            provider: TokenProvider::class,
        ),
        new Get(
            uriTemplate: '/auth/token/{hash}/{auth_type}',
            stateless: true,
            openapi: new Operation(
                summary: 'Get auth tokens  by types',
                description: 'Get auth tokens by types',
                parameters: [
                    new Parameter(
                        name: 'hash',
                        in: 'path',
                        description: 'Get token by type and provided hash',
                        required: true,
                    ),
                ]
            ),
            output: TokenResponse::class,
            provider: TokenByTypeProvider::class,
        ),
        new Get(
            uriTemplate: '/auth/cms',
            stateless: false,
            openapi: new Operation(
                summary: 'Auth via cms',
                description: 'Auth via cms',
                parameters: [
                    new Parameter(
                        name: 'uid',
                        in: 'query',
                        description: 'Token provided by google',
                        required: false,
                        example: '01HPQ2XMABBJXKGQ8Z6H8MMPJM',
                    ),
                    new Parameter(
                        name: 'sign',
                        in: 'query',
                        description: 'Signature salt',
                        required: false,
                        example: 'a135-p428gphru9god9pg-ya00p493utg3ohi-2iho948',
                    ),
                ]
            ),
            output: HashResponse::class,
            provider: AuthCmsProvider::class,
        ),
        new Get(
            uriTemplate: '/auth/google',
            stateless: false,
            openapi: new Operation(
                summary: 'Auth via google',
                description: 'Google redirect authorized user here.',
            ),
            output: RedirectResponse::class,
            name: 'google_auth',
            provider: AuthGoogleProvider::class,
        ),
        new Get(
            uriTemplate: '/auth/google/token',
            stateless: false,
            openapi: new Operation(
                summary: 'Auth via google token',
                description: 'App .',
                parameters: [
                    new Parameter(
                        name: 'token',
                        in: 'query',
                        description: 'Token provided by google',
                        required: false,
                        example: 'a135-p428gphru9god9pg-ya00p493utg3ohi-2iho948',
                    ),
                ]
            ),
            output: TokenResponse::class,
            provider: AuthGoogleTokenProvider::class,
        ),
        new Get(
            uriTemplate: '/auth/connect/google',
            stateless: false,
            openapi: new Operation(
                summary: 'Redirect to google',
                description: 'Redirect user to google auth form.',
            ),
            output: RedirectResponse::class,
            provider: ConnectGoogleProvider::class,
        ),
    ],
)]
class AuthController extends AbstractController
{
}
