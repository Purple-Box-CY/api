<?php

namespace App\Provider\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiDTO\Response\Auth\TokenResponse;
use App\Exception\Http\AccessDenied\AccessDeniedToObjectHttpException;
use App\Exception\Http\BadRequest\NotAcceptableValueException;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\User\Service\UserAuthService;
use App\User\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;

class TokenByTypeProvider implements ProviderInterface
{
    public function __construct(
        private readonly RedisService      $redisService,
        private readonly UserService       $userService,
        private readonly RequestStack      $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TokenResponse
    {
        $this->redisService->setPrefix(RedisKeys::PREFIX_AUTH);
        $hash = $uriVariables['hash'];
        $token = $this->redisService->get($hash);
        $authType = $uriVariables['auth_type'] ?? null;

        if (!$token) {
            throw new BadRequestException('Token not found');
        }

        if (!$authType) {
            throw new BadRequestException('Auth type is required');
        }

        if (!in_array($authType, UserAuthService::AVAILABLE_AUTH_TYPES)) {
            throw new NotAcceptableValueException('Auth type is not acceptable', 406);
        }

        $token = json_decode($token, true);

        $userUid = $token[TokenResponse::FIELD_USER_UID] ?? null;
        if ($userUid == 'no_user') {
            $userUid = null;
        }

        $currentUserUid = $token[TokenResponse::FIELD_CURRENT_USER_UID];

        $request = $this->requestStack->getCurrentRequest();
        $user = $this->userService->getUserByUid($currentUserUid, false);

        if (!$user->isAnonym() && $authType === UserAuthService::AUTH_TYPE_ANONYM) {
            throw new AccessDeniedToObjectHttpException('Access is denied to auth');
        }

        //$this->userService->updateUserWithRegisteredUser(
        //    $user,
        //    $request->headers->get(RequestService::HEADER_USER_ID),
        //    $request->getClientIp(),
        //    $request->headers->get(RequestService::HEADER_USER_AGENT),
        //);

        $isJustRegistered = $token[TokenResponse::FIELD_IS_JUST_REGISTERED] ?? false;

        return new TokenResponse(
            token: $token[TokenResponse::FIELD_TOKEN],
            refreshToken: $token[TokenResponse::FIELD_REFRESH_TOKEN],
            streamToken: $token[TokenResponse::FIELD_STREAM_TOKEN],
            action: $isJustRegistered ? 'registration' : 'login',
            source: $token[TokenResponse::FIELD_SOURCE] ?? null,
            userUid: $userUid,
            userType: $user->getUserType(),
        );
    }
}
