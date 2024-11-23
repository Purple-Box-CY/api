<?php

namespace App\User\Processor\Me;

use App\User\DTO\Request\Me\RequestEditMeGeoInfoDTO;
use App\User\DTO\Response\Me\ResponseMeMainInfoDTO;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class GeoInfoProcessor implements ProcessorInterface
{
    public function __construct(
        private UserService            $userService,
    ) {
    }

    /**
     * @param RequestEditMeGeoInfoDTO $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $currentUser = $this->userService->getCurrentUser();
        $this->userService->checkUser($currentUser);

        $user = $this->userService->getUserById($currentUser->getId());

        $user->getInfo()->setCountry($data->country);
        if ($data->city) {
            $user->getInfo()->setCity($data->city);
        }

        $this->userService->saveUser($user);

        return new JsonResponse(
            data: ResponseMeMainInfoDTO::create(
                user: $user,
            ),
            status: Response::HTTP_OK
        );
    }
}
