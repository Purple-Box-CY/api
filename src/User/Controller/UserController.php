<?php

namespace App\User\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\ApiDTO\Request\Base\EmptyRequest;
use App\ApiDTO\Response\Base\EmptyResponse;
use App\User\DTO\Request\RequestAuthDTO;
use App\User\DTO\Request\RequestNewPasswordDTO;
use App\User\DTO\Request\RequestRegisterDTO;
use App\User\DTO\Request\RequestResendConfirmationDTO;
use App\User\DTO\Request\RequestResetPasswordDTO;
use App\User\DTO\Request\RequestUpdatePasswordDTO;
use App\User\DTO\Response\Me\ResponseMeInfoDTO;
use App\User\DTO\Response\ResponseAuthDTO;
use App\User\DTO\Response\ResponseRegisterDTO;
use App\User\Processor\AuthProcessor;
use App\User\Processor\LogoutProcessor;
use App\User\Processor\NewPasswordProcessor;
use App\User\Processor\RegisterProcessor;
use App\User\Processor\ResendConfirmationEmailProcessor;
use App\User\Processor\ResetPasswordProcessor;
use App\User\Processor\UpdatePasswordProcessor;
use App\User\Provider\MeInfoProvider;
use App\User\Provider\RedirectToResetPasswordProvider;
use App\User\Provider\VerifyProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[ApiResource(
    shortName: 'User Auth',
    operations: [
        new Post(
            uriTemplate: '/user/register',
            openapi: new Operation(
                summary: 'Registration',
                description: 'Registration new user',
            ),
            input: RequestRegisterDTO::class,
            output: ResponseRegisterDTO::class,
            processor: RegisterProcessor::class,
        ),
        new Post(
            uriTemplate: '/user/auth',
            openapi: new Operation(
                summary: 'Auth by user',
                description: 'Auth by user',
            ),
            input: RequestAuthDTO::class,
            output: ResponseAuthDTO::class,
            processor: AuthProcessor::class,
        ),
        new Post(
            uriTemplate: '/user/reset-password',
            openapi: new Operation(
                summary: 'Send email with password reset link',
            ),
            input: RequestResetPasswordDTO::class,
            output: ResponseRegisterDTO::class,
            processor: ResetPasswordProcessor::class,
        ),
        new Get(
            uriTemplate: '/user/check-reset-password-token/{sign}',
            output: RedirectResponse::class,
            name: 'app_check_reset_password_token',
            provider: RedirectToResetPasswordProvider::class,
        ),
        new Post(
            uriTemplate: '/user/change-password',
            openapi: new Operation(
                summary: 'Reset user password via email link',
            ),
            input: RequestNewPasswordDTO::class,
            processor: NewPasswordProcessor::class,
        ),
        new Post(
            uriTemplate: '/user/update-password',
            stateless: false,
            status: 204,
            openapi: new Operation(
                summary: 'Reset user password by authorized user',
            ),
            input: RequestUpdatePasswordDTO::class,
            processor: UpdatePasswordProcessor::class,
        ),
        new Post(
            uriTemplate: '/user/confirmation/resend',
            stateless: true,
            status: 202,
            openapi: new Operation(
                summary: 'Resend confirmation email',
            ),
            input: RequestResendConfirmationDTO::class,
            processor: ResendConfirmationEmailProcessor::class,
        ),
        new Get(
            uriTemplate: '/user/verify',
            stateless: false,
            openapi: new Operation(
                summary: 'Verify email',
                parameters: [
                    new Parameter(
                        name: 'expires',
                        in: 'query',
                        description: 'Token expires date',
                        required: true,
                    ),
                    new Parameter(
                        name: 'signature',
                        in: 'query',
                        description: 'Token signature',
                        required: true,
                    ),
                    new Parameter(
                        name: 'token',
                        in: 'query',
                        description: 'Token',
                        required: true,
                    ),
                    new Parameter(
                        name: 'uid',
                        in: 'query',
                        description: 'User uid',
                        required: true,
                    ),
                ]
            ),
            output: RedirectResponse::class,
            name: 'app_verify_email',
            provider: VerifyProvider::class,
        ),
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
        new Post(
            uriTemplate: '/user/logout',
            openapi: new Operation(
                summary: 'Logout',
                description: 'User Logout',
            ),
            input: EmptyRequest::class,
            output: EmptyResponse::class,
            processor: LogoutProcessor::class,
        ),
    ],
)]
class UserController extends AbstractController
{
}

