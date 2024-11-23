<?php

namespace App\User\DTO\Request\Me;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class RequestUploadAvatarDTO
{
    public function __construct(
        #[Assert\File(
            maxSize: '15M',
            mimeTypes: [
                'image/gif',
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/heic',
            ],
            filenameMaxLength: 256,
        )]
        #[Assert\NotNull]
        #[Assert\NotBlank]
        //#[Assert\Image(
        //    minWidth: UserService::AVATAR_CROP_WIDTH,
        //    minHeight: UserService::AVATAR_CROP_HEIGHT,
        //)]
        public File $file,
    ) {}
}