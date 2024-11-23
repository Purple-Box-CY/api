<?php

namespace App\Provider\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiDTO\Response\Auth\TokenResponse;
use App\Entity\Settings\Country;
use App\EventListener\AuthenticationSuccessListener;
use App\Security\Infrastructure\GoogleAuthenticator;
use App\Service\Infrastructure\LogService;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\Utility\DomainHelper;
use App\Service\Utility\MomentHelper;
use App\User\Entity\User;
use App\User\Service\UserAuthService;
use App\User\Service\UserService;
use Gesdinet\JWTRefreshTokenBundle\EventListener\AttachRefreshTokenOnSuccessListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthGoogleProvider implements ProviderInterface
{

    public function __construct(
        private string                                       $webProjectDomain,
        private readonly UserService                         $userService,
        private readonly RedisService                        $redisService,
        private readonly AttachRefreshTokenOnSuccessListener $attachRefreshTokenOnSuccessListener,
        private readonly JWTTokenManagerInterface            $jwtManager,
        private readonly GoogleAuthenticator                 $googleAuthenticator,
        private readonly RequestStack                        $requestStack,
        private readonly EventDispatcherInterface            $eventDispatcher,
        private readonly AuthenticationSuccessListener       $authenticationSuccessListener,
        private readonly LogService                          $logService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): RedirectResponse
    {
        try {
            $currentUser = $this->userService->getCurrentUser();
            $state = $context['filters']['state'] ?? ',';
            [$userUid, $country] = explode(',', $state);

            $passport = $this->googleAuthenticator->authenticate(
                $this->requestStack->getCurrentRequest(),
                $userUid,
            );

            /**
             * @var User $user
             */
            $user = $passport->getUser();
            if ($user->isDeleted()) {

                return new RedirectResponse($this->webProjectDomain.'/user/signin?status=fail&error=user_is_deleted');
            }

            $jwt = $this->jwtManager->create($user);
            $response = new JWTAuthenticationSuccessResponse($jwt);

            $event = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);

            $this->eventDispatcher->dispatch($event);

            $this->attachRefreshTokenOnSuccessListener->attachRefreshToken($event);

            $this->authenticationSuccessListener->onAuthenticationSuccessResponse($event);

            //$contentId = $context['filters']['state'] ?? '';
            //$country = $context['filters']['country'] ?? null;
            if ($country && isset(Country::COUNTRIES[$country])) {
                $user->getInfo()->setCountry($country);
                $user = $this->userService->saveUser($user);
            }

            $data = $event->getData();
            $data[TokenResponse::FIELD_USER_UID] = $userUid;
            $data[TokenResponse::FIELD_IS_JUST_REGISTERED] = $user->isJustRegistered();
            $data[TokenResponse::FIELD_SOURCE] = $user->getSource();

            $hash = md5(random_bytes(32));
            $ttl = $user->isAnonym() ? MomentHelper::SECONDS_MONTH : MomentHelper::SECONDS_HOUR;

            $this->redisService->setPrefix(RedisKeys::PREFIX_AUTH);
            $this->redisService->set(
                $hash,
                json_encode($data),
                $ttl,
            );

            return new RedirectResponse($this->webProjectDomain.'/user/auth?hash='.$hash.'&auth_type='.UserAuthService::AUTH_TYPE_GOOGLE);
        } catch (\Exception $e) {

            $this->logService->error('Failed to get google user', ['error' => $e->getMessage()]);

            return new RedirectResponse(DomainHelper::getWebProjectDomain().'/user/signin?status=fail&error=error_runtime');
        }
    }
}
