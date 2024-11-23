<?php

namespace App\ApiDTO\Response\Camera;

class ResponseUploadImageDTO
{
    public function __construct(
        #[ApiProperty(example: 'terms_of_service')]
        public string $alias,
    ) {
    }

    public static function create(
    ): self {
        return new self(
            alias: '1',
        );
    }
}