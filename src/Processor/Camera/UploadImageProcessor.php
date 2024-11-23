<?php

namespace App\Processor\Camera;

use App\Exception\Http\NotFound\FailedToGetUserHttpException;
use App\Service\OpenAiService;
use App\User\DTO\Request\Me\RequestUploadAvatarDTO;
use App\User\DTO\Response\Me\ResponseMeMainInfoDTO;
use App\User\Entity\User;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class UploadImageProcessor implements ProcessorInterface
{

    public function __construct(
        private OpenAiService $openAIService,
    ) {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        if (!isset($data->file) || !$data->file) {
            throw new BadRequestException('File is required');
        }

        $file = $data->file;

        $fileContent = file_get_contents($file->getRealPath());
        $base64File = base64_encode($fileContent);


        try {
            $response = $this->openAIService->processImage($base64File);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to process image: ' . $e->getMessage());
        }

        return new JsonResponse(
            data: [
                'success' => true,
                'data' => $response
            ],
            status: Response::HTTP_OK
        );
    }
}
