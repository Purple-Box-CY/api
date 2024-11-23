<?php

namespace App\Provider\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiDTO\Response\Auth\TokenResponse;
use App\Service\Infrastructure\LogService;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\User\Service\UserAuthService;
use App\User\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;

class TokenProvider implements ProviderInterface
{

    public function __construct(
        private RedisService      $redisService,
        private UserService       $userService,
        private RequestStack      $requestStack,
        private UserAuthService   $userAuthService,
        private LogService        $logService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TokenResponse
    {
        $this->redisService->setPrefix(RedisKeys::PREFIX_AUTH);
        $hash = $uriVariables['hash'];
        $token = $this->redisService->get($hash);

        if (!$token) {
            throw new BadRequestException('Token not found');
        }

        $token = json_decode($token, true);

        $userUid = $token[TokenResponse::FIELD_USER_UID] ?? null;
        if ($userUid == 'no_user') {
            $userUid = null;
        }

        $currentUserUid = $token[TokenResponse::FIELD_CURRENT_USER_UID];

        $request = $this->requestStack->getCurrentRequest();
        $user = $this->userService->getUserByUid($currentUserUid, false);
        //$this->userService->updateUserWithRegisteredUser(
        //    $user,
        //    $request->headers->get(AnonymService::HEADER_USER_ID),
        //    $request->getClientIp(),
        //    $request->headers->get(AnonymService::HEADER_USER_AGENT),
        //);

        $isJustRegistered = $token[TokenResponse::FIELD_IS_JUST_REGISTERED] ?? false;

        try {
            $newToken = $this->userAuthService->createAuthData($user);
            $token = $newToken;
        } catch (\Exception $e) {
            $this->logService->error('Failed to updated token data by hash',
                [
                    'error'    => $e->getMessage(),
                    'user_uid' => $user->getUid(),
                    'hash'     => $hash,
                    'method'   => __METHOD__,
                ]);
        }

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
