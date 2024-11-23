<?php

namespace App\User\Processor\Me;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\Infrastructure\LogService;
use App\User\DTO\Request\Me\RequestEditMeInfoDTO;
use App\User\DTO\Response\Me\ResponseMeMainInfoDTO;
use App\User\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class MainInfoProcessor implements ProcessorInterface
{
    public function __construct(
        private UserService $userService,
        private LogService  $logger,
    ) {
    }

    /**
     * @param RequestEditMeInfoDTO $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $currentUser = $this->userService->getCurrentUser();
        $this->userService->checkUser($currentUser);

        if ($currentUser->getUsername() !== $data->username && $this->userService->getUserByUsername($data->username)) {
            throw new BadRequestException(sprintf('Username %s is already taken', $data->username));
        }

        $user = $this->userService->getUserById($currentUser->getId());

        $user->setUsername($data->username);
        $user->setFullName($data->fullName);

        $birthDate = new \DateTimeImmutable($data->birthDate);
        $user->getInfo()->setBirthDate($birthDate);
        $user->getInfo()->setDescription($data->description);
        $user->getInfo()->setSex($data->gender);
        $user->getInfo()->setCountry($data->country);
        $user->getInfo()->setCity($data->city);
        $user->getInfo()->setLanguage($data->language);
        $user->setSlogan($data->slogan);

        try {
            $this->userService->saveUser($user);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save user info profile',
                [
                    'method'            => __METHOD__,
                    'error_message'     => $e->getMessage(),
                    'error_stack_trace' => $e->getTrace(),
                    'user_uid'          => $user->getUidStr(),
                ]);
        }

        return new JsonResponse(
            data: ResponseMeMainInfoDTO::create(
                user: $user,
            ),
            status: Response::HTTP_OK,
        );
    }
}
