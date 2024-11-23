<?php

namespace App\User\Processor\Me;

use App\Exception\Http\NotFound\FailedToGetUserHttpException;
use App\User\DTO\Request\Me\RequestUploadAvatarDTO;
use App\User\DTO\Response\Me\ResponseMeMainInfoDTO;
use App\User\Entity\User;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class UploadAvatarProcessor implements ProcessorInterface
{
    public function __construct(
        private UserService            $userService,
    ) {
    }

    /**
     * @param RequestUploadAvatarDTO $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $user = $this->userService->getCurrentUser();
        $user = $this->userService->getUserById($user->getId());

        if (!($user instanceof User)) {
            throw new FailedToGetUserHttpException();
        }

        if (!isset($data->file) || !$data->file) {
            throw new BadRequestException('File is required');
        }

        $user = $this->userService->uploadUserAvatar($user, $data->file);

        return new JsonResponse(
            data: ResponseMeMainInfoDTO::create(
                user: $user,
            ),
            status: Response::HTTP_OK
        );
    }
}
