<?php

namespace App\ApiDTO\Response\Settings;

use ApiPlatform\Metadata\ApiProperty;

class LanguageDTO
{
    public function __construct(
        #[ApiProperty(example: 'EN')]
        public string $code = '',
        #[ApiProperty(example: 'English')]
        public string $name = '',
    ) {
    }

    public static function create(
        string $code,
        string $name,
    ): self {
        return new self(
            code: $code,
            name: $name,
        );
    }
}
