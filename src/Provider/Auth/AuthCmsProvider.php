<?php

namespace App\Provider\Auth;

use App\ApiDTO\Response\Auth\HashResponse;
use App\Exception\Http\AccessDenied\PasswordIsNotCorrectException;
use App\Exception\Http\BadRequest\MissingRequiredRequestParameterException;
use App\Exception\Http\NotFound\UserNotFoundHttpException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\User\Service\UserAuthService;
use App\User\Service\UserService;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthCmsProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserAuthService $userAuthService,
        private readonly UserService     $userService,
        private readonly RequestStack    $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): HashResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $userUid = $request->get('uid');
        $sign = $request->get('sign');
        if (!$userUid || !$sign) {
            throw new MissingRequiredRequestParameterException();
        }

        if (!$this->userAuthService->checkPass($sign)) {
            throw new PasswordIsNotCorrectException();
        }

        $user = $this->userService->getUserByUid($userUid, false);
        if (!$user) {
            throw new UserNotFoundHttpException();
        }

        return new HashResponse(
            hash: $this->userAuthService->generateAuthHash(
                user: $user,
                authType: UserAuthService::AUTH_TYPE_CMS
            ),
        );
    }
}
