<?php

namespace App\User\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiDTO\Response\Auth\TokenResponse;
use App\EventListener\AuthenticationSuccessListener;
use App\Security\Infrastructure\AppJsonLoginAuthenticator;
use App\User\DTO\Request\RequestRegisterDTO;
use App\User\DTO\Response\ResponseRegisterDTO;
use App\User\Entity\User;
use App\User\Service\UserAuthService;
use App\User\Service\UserEmailService;
use App\User\Service\UserService;
use Doctrine\DBAL\Exception;
use Gesdinet\JWTRefreshTokenBundle\EventListener\AttachRefreshTokenOnSuccessListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final class RegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserService                         $userService,
        private readonly JWTTokenManagerInterface            $jwtManager,
        private readonly EventDispatcherInterface            $eventDispatcher,
        private readonly AttachRefreshTokenOnSuccessListener $attachRefreshTokenOnSuccessListener,
        private readonly AuthenticationSuccessListener       $authenticationSuccessListener,
        private readonly UserEmailService                    $userEmailService,
        private readonly UserAuthService                     $userAuthService,
        private readonly AppJsonLoginAuthenticator           $appJsonLoginAuthenticator,
        private readonly RequestStack                        $requestStack,
    ) {}

    /**
     * @param RequestRegisterDTO $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse|TokenResponse
    {
        $currentUser = $this->userService->getCurrentUser();
        $data->email = mb_strtolower($data->email);

        $user = $this->userService->getUserByEmail($data->email);

        if ($user && !$user->isDeleted()) {
            //проверяем пароль, пробуем залогинеть
            if (!$this->userAuthService->checkPassword($user, $data->password)) {
                return new JsonResponse(
                    data: ['error' => 'Password is incorrect'],
                    status: Response::HTTP_BAD_REQUEST
                );
            }

            $this->appJsonLoginAuthenticator->setOption('username_path', 'email');
            try {
                $passport = $this->appJsonLoginAuthenticator->authenticate($this->requestStack->getCurrentRequest());
            } catch (Exception $e) {
                $passport = null;
            }

            if ($passport && $userToken = $passport->getUser()) {
                /** @var User $userToken */
                return $this->userAuthService->getTokenResponse($userToken);
            }

            return new JsonResponse(
                data: ['error' => sprintf('User by email %s already exists', $data->email)],
                status: Response::HTTP_BAD_REQUEST
            );
        }

        if ($user && $user->isDeleted()) {
            $this->userService->renameDeletedUser($user);
        }

        if (!$data->name) {
            $data->name = $this->userEmailService->generateUsernameByEmail($data->email);
        }

        if ($this->userService->getUserByUsername($data->name)) {
            return new JsonResponse(
                data: ['error' => sprintf('User by name %s already exists', $data->name)],
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $request = $this->requestStack->getCurrentRequest();
        $regPoint = $request->get('reg_point') ?? '';
        $regSource = $data->source;

        if ($currentUser && $user = $this->userService->getUserById($currentUser->getId())) {
            //
        } else {
            $fromUserUid = $data->userUid;

            if ($user && $user->isDeleted()) {
                $this->userService->renameDeletedUser($user);
            }

            $user = $this->userService->createUser(
                email: $data->email,
                name: $data->name,
                password: $data->password,
                regPoint: $regPoint,
                fromUserUid: $fromUserUid,
                regSource: $regSource
            );
        }

        $request = $this->requestStack->getCurrentRequest();
        //$this->userService->updateUserWithRegisteredUser(
        //    $user,
        //    $request->headers->get(RequestService::HEADER_USER_ID),
        //    $request->getClientIp(),
        //    $request->headers->get(RequestService::HEADER_USER_AGENT),
        //);

        if ($data->country) {
            $user->getInfo()->setCountry($data->country);
            $this->userService->saveUser($user);
        }

        $this->userEmailService->sendConfirmationEmail(
            user: $user,
            contentId: $data->contentId
        );

        $jwt = $this->jwtManager->create($user);
        $response = new JWTAuthenticationSuccessResponse($jwt);

        $event = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);

        $this->eventDispatcher->dispatch($event);

        $this->attachRefreshTokenOnSuccessListener->attachRefreshToken($event);
        $this->authenticationSuccessListener->onAuthenticationSuccessResponse($event);

        return new JsonResponse(
            data: new ResponseRegisterDTO(
                message: "Registered Successfully",
                token: $event->getData()[TokenResponse::FIELD_TOKEN],
                refreshToken: $event->getData()[TokenResponse::FIELD_REFRESH_TOKEN],
                streamToken: $event->getData()[TokenResponse::FIELD_STREAM_TOKEN],
                userType: $user->getUserType(),
                uid: $event->getData()[TokenResponse::FIELD_CURRENT_USER_UID] ?? null,
            ),
            status: Response::HTTP_CREATED
        );
    }
}
