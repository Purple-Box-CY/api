<?php

namespace App\Processor\Camera;

use App\Exception\Http\NotFound\FailedToGetUserHttpException;
use App\Service\OpenAiService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
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

        // Ensure the response includes both 'category' and 'value'
        $responseData = [
            'category' => $response['category'] ?? 'None',
            'value' => $response['value'] ?? null,
        ];

        return new JsonResponse(
            data: [
                'success' => true,
                'data' => $responseData
            ],
            status: Response::HTTP_OK
        );
    }
}
