<?php

namespace App\Security\Infrastructure;

use App\User\Domain\UserFactory;
use App\User\Entity\User;
use App\User\Service\UserEmailService;
use App\User\Service\UserEventService;
use App\User\Service\UserService;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntrypointInterface
{
    public function __construct(
        private UserService      $userService,
        private UserEventService $userEventService,
        private UserEmailService $userEmailService,
        private UserFactory      $userFactory,
        private ClientRegistry   $clientRegistry,
        private RouterInterface  $router,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'google_auth';
    }

    public function authenticate(
        Request $request,
        ?string $anonUid = null,
    ): Passport {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(),
                function () use ($accessToken, $client, $anonUid) {
                    /** @var GoogleUser $googleUser */
                    $googleUser = $client->fetchUserFromToken($accessToken);

                    return $this->getOrCreateUserByGoogleUser(
                        $googleUser,
                        $anonUid,
                    );
                }),
        );
    }

    public function getOrCreateUserByGoogleUser(
        GoogleUser $googleUser,
        ?string    $anonUid = null,
    ): User {
        $email = $googleUser->getEmail();
        $fullName = $googleUser->getName();

        $currentUser = $this->userService->getCurrentUser();
        $existingUser = $this->userService->getUserByGoogleId($googleUser->getId());
        if ($existingUser) {
            return $existingUser;
        }

        $user = $this->userService->getUserByEmail($email);

        if (!$user) {
            $username = $this->userEmailService->generateUsernameByEmail($email);

            $anonUser = $anonUid ? $this->userService->getUserByUid($anonUid, false) : null;
            if ($anonUser && $anonUser->isAnonym()) {
                //
            } else {
                $user = $this->userFactory->create(
                    email: $email,
                    username: $username,
                    fullName: $fullName,
                );
            }
            $user->setIsJustRegistered(true);
            $user
                ->setSource(User::SOURCE_GOOGLE)
                ->setIsVerified(true)
                ->setAvatar($googleUser->getAvatar());
        }

        $user->setGoogleId($googleUser->getId());

        $this->userService->saveUser($user);

        $this->userEventService->sendEventCreateUser($user);

        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetUrl = $this->router->generate('_api_/feed_get');

        return new RedirectResponse($targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            '/connect/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT,
        );
    }
}
