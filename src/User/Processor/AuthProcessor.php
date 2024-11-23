<?php

namespace App\User\Processor;

use ApiPlatform\Metadata\Operation;
use App\ApiDTO\Response\Auth\TokenResponse;
use App\EventListener\AuthenticationSuccessListener;
use App\Exception\Http\BadRequest\MissingRequiredRequestParameterException;
use App\User\DTO\Request\RequestAuthDTO;
use App\User\DTO\Response\ResponseAuthDTO;
use App\User\Service\UserAuthService;
use ApiPlatform\State\ProcessorInterface;
use App\User\Service\UserService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Gesdinet\JWTRefreshTokenBundle\EventListener\AttachRefreshTokenOnSuccessListener;
use Symfony\Component\Uid\Ulid;

final class AuthProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserAuthService $authService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AttachRefreshTokenOnSuccessListener $attachRefreshTokenOnSuccessListener,
        private readonly AuthenticationSuccessListener $authenticationSuccessListener,
    ) {}

    /**
     * @param RequestAuthDTO $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        if (!$data->email) {
            throw new MissingRequiredRequestParameterException('Missing required parameter email');
        }

        $user = null;
        if (Ulid::isValid($data->email)) {
            $user = $this->userService->getUserByUid($data->email, false);
        }

        if (!$user) {
            $user = $this->userService->getUserByEmail($data->email);
            if (!$user) {
                return new JsonResponse(
                    data: ['error' => sprintf('User by email %s not found', $data->email)],
                    status: Response::HTTP_NOT_FOUND
                );
            }
        }

        if (!$this->authService->checkPass($data->password)) {
            return new JsonResponse(
                data: ['error' => 'Password is not correct'],
                status: Response::HTTP_FORBIDDEN
            );
        }

        if ($data->authType) {
            $user->addRole('auth_type_'.$data->authType);
        }

        $jwt = $this->jwtManager->create($user);
        $response = new JWTAuthenticationSuccessResponse($jwt);

        $event = new AuthenticationSuccessEvent(['token' => $jwt], $user, $response);
        $this->eventDispatcher->dispatch($event);

        $this->attachRefreshTokenOnSuccessListener->attachRefreshToken($event);
        $this->authenticationSuccessListener->onAuthenticationSuccessResponse($event);

        return new JsonResponse(
            data: new ResponseAuthDTO(
                token: $event->getData()[TokenResponse::FIELD_TOKEN],
                refreshToken: $event->getData()[TokenResponse::FIELD_REFRESH_TOKEN],
                streamToken: $event->getData()[TokenResponse::FIELD_STREAM_TOKEN],
                userType: $user->getUserType(),
            ),
            status: Response::HTTP_OK
        );
    }
}
