<?php

namespace App\User\Service;

use App\ApiDTO\Response\Auth\TokenResponse;
use App\EventListener\AuthenticationSuccessListener;
use App\Security\Infrastructure\AppJWTAuthenticator;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\Utility\MomentHelper;
use App\User\Domain\UserPasswordHasherInterface;
use App\User\Entity\Interfaces\AppUserInterface as UserInterface;
use App\User\Entity\User;
use Gesdinet\JWTRefreshTokenBundle\EventListener\AttachRefreshTokenOnSuccessListener;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class UserAuthService
{
    public const AUTH_TYPE_CMS            = 'cms';
    public const AUTH_TYPE_GOOGLE         = 'google';
    public const AUTH_TYPE_FACEBOOK       = 'facebook';
    public const AUTH_TYPE_INSTAGRAM      = 'instagram';
    public const AUTH_TYPE_ANONYM         = 'anonym';
    public const AUTH_TYPE_EMAIL_PASSWORD = 'email_password';
    public const AVAILABLE_AUTH_TYPES     = [
        self::AUTH_TYPE_CMS,
        self::AUTH_TYPE_GOOGLE,
        self::AUTH_TYPE_FACEBOOK,
        self::AUTH_TYPE_INSTAGRAM,
        self::AUTH_TYPE_ANONYM,
        self::AUTH_TYPE_EMAIL_PASSWORD,
    ];

    public function __construct(
        private string                              $authPass,
        private JWTEncoderInterface                 $jwtEncoder,
        public AppJWTAuthenticator                  $appJWTAuthenticator,
        private JWTTokenManagerInterface            $jwtManager,
        private EventDispatcherInterface            $eventDispatcher,
        private AttachRefreshTokenOnSuccessListener $attachRefreshTokenOnSuccessListener,
        private AuthenticationSuccessListener       $authenticationSuccessListener,
        private UserPasswordHasherInterface         $passwordHasher,
        private RedisService                        $redisService,
    ) {
    }

    public function checkPass(string $pass): bool
    {
        if (!$this->authPass) {
            return false;
        }

        return $this->authPass === $pass;
    }

    public function getUserByToken(string $token): ?UserInterface
    {
        $payload = $this->jwtEncoder->decode($token);
        $email = $payload['email'] ?? null;
        if (!$email) {
            return null;
        }

        /** @var User $userInfo */
        $userInfo = $this->appJWTAuthenticator->getUserByToken($payload, $email);

        return $userInfo;
    }

    public function createAuthData(User $user, ?string $authType = null): array
    {
        if ($authType) {
            $user->addRole('auth_type_'.$authType);
        }

        $jwt = $this->jwtManager->create($user);
        $response = new JWTAuthenticationSuccessResponse($jwt);

        $event = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);

        $this->eventDispatcher->dispatch($event);

        $this->attachRefreshTokenOnSuccessListener->attachRefreshToken($event);

        $this->authenticationSuccessListener->onAuthenticationSuccessResponse($event);

        return $event->getData();
    }

    public function getTokenResponse(User $user): TokenResponse
    {
        $eventData = $this->createAuthData($user);

        return new TokenResponse(
            token: $eventData[TokenResponse::FIELD_TOKEN],
            refreshToken: $eventData[TokenResponse::FIELD_REFRESH_TOKEN],
            streamToken: $eventData[TokenResponse::FIELD_STREAM_TOKEN],
            userType: $user->getUserType(),
            uid: $eventData['uid'] ?? null,
        );
    }

    public function checkPassword(User $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    public function generateAuthHash(
        User    $user,
        array   $data = [],
        ?string $hash = null,
        ?string $authType = null,
    ): string {
        $eventData = $this->createAuthData($user, $authType);

        if (!$hash) {
            $hash = md5(random_bytes(32));
        }
        $ttl = $user->isAnonym() ? MomentHelper::SECONDS_MONTH : MomentHelper::SECONDS_5_MINUTES;

        $this->redisService->setPrefix(RedisKeys::PREFIX_AUTH);
        $this->redisService->set(
            $hash,
            json_encode(array_merge($eventData, $data)),
            $ttl,
        );

        return $hash;
    }
}
