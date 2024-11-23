<?php

namespace App\Provider\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiDTO\Response\Auth\TokenResponse;
use App\EventListener\AuthenticationSuccessListener;
use App\Exception\Http\AccessDenied\UserIsDeletedException;
use App\Security\Infrastructure\GoogleAuthenticator;
use Gesdinet\JWTRefreshTokenBundle\EventListener\AttachRefreshTokenOnSuccessListener;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\GoogleUser;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AuthGoogleTokenProvider implements ProviderInterface
{

    public function __construct(
        private readonly AttachRefreshTokenOnSuccessListener $attachRefreshTokenOnSuccessListener,
        private readonly AuthenticationSuccessListener       $authenticationSuccessListener,
        private readonly JWTTokenManagerInterface            $jwtManager,
        private readonly GoogleAuthenticator                 $googleAuthenticator,
        private readonly EventDispatcherInterface            $eventDispatcher,
    ) {
    }

    public function provide(
        Operation $operation,
        array     $uriVariables = [],
        array     $context = []
    ): TokenResponse|BadRequestException {
        $anonUid = $context['filters']['a_uid'] ?? 'no_anon';
        $regPoint = $context['filters']['reg_point'] ?? 'no_reg_point';
        $regSource = $context['filters']['source'] ?? 'no_reg_source';
        $fromUserUid = $context['filters']['a_uid'] ?? null;

        $guzzleClient = new Client();
        $response = $guzzleClient->get('https://oauth2.googleapis.com/tokeninfo?id_token='.$context['filters']['token']);
        /** @var array $body
         * @example {
         * "iss": "https://accounts.google.com",
         * "azp": "90013871521-8la4epec5n1gfeiqd226knhphuf9b3j0.apps.googleusercontent.com",
         * "aud": "90013871521-nbggr47evo4qpqje2c4b3uqtq199d5fb.apps.googleusercontent.com",
         * "sub": "108741714320167420247",
         * "email": "ivanterechov@gmail.com",
         * "email_verified": "true",
         * "at_hash": "kLXGpVGmues3rJUBdku3WA",
         * "nonce": "jsiA6NQpByJXt4KdNaZdkeCny5PeTPreZKph19TmWOw",
         * "name": "Ivan Terekhov",
         * "picture": "https://lh3.googleusercontent.com/a/ACg8ocL_2Do5nAc3ONbIAct-lDODDwBKtRx1Atk9HsYZgzDkjg=s96-c",
         * "given_name": "Ivan",
         * "family_name": "Terekhov",
         * "locale": "ru",
         * "iat": "1706681003",
         * "exp": "1706684603",
         * "alg": "RS256",
         * "kid": "85e5510d466b7e29836199c58c7581f5b923be44",
         * "typ": "JWT"
         * }
         */
        $body = json_decode($response->getBody()->getContents(), true);
        $googleUser = new GoogleUser($body);
        $user = $this->googleAuthenticator->getOrCreateUserByGoogleUser(
            $googleUser,
            $anonUid,
            $regPoint,
            $fromUserUid,
            $regSource
        );
        if ($user->isDeleted()) {
            throw new UserIsDeletedException();
        }
        $jwt = $this->jwtManager->create($user);
        $response = new JWTAuthenticationSuccessResponse($jwt);

        $event = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);

        $this->eventDispatcher->dispatch($event);

        $this->attachRefreshTokenOnSuccessListener->attachRefreshToken($event);

        $this->authenticationSuccessListener->onAuthenticationSuccessResponse($event);

        return new TokenResponse(
            token: $event->getData()[TokenResponse::FIELD_TOKEN],
            refreshToken: $event->getData()[TokenResponse::FIELD_REFRESH_TOKEN],
            streamToken: $event->getData()[TokenResponse::FIELD_STREAM_TOKEN],
            userType: $user->getUserType(),
            uid: $event->getData()[TokenResponse::FIELD_CURRENT_USER_UID] ?? null,
        );
    }
}
