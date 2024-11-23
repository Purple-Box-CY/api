<?php

namespace App\Controller\Action;

use ApiPlatform\Validator\ValidatorInterface;
use App\User\DTO\Request\Me\RequestUploadAvatarDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
final class CameraUploadAction extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    public function __invoke(Request $request): RequestUploadAvatarDTO
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $requestDTO = new RequestUploadAvatarDTO(file: $uploadedFile);
        $this->validator->validate($requestDTO);

        return $requestDTO;
    }
}
