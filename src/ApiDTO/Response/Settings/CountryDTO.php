<?php

namespace App\ApiDTO\Response\Settings;

use ApiPlatform\Metadata\ApiProperty;

class CountryDTO
{
    public function __construct(
        #[ApiProperty(example: 'CY')]
        public string $code = '',
        #[ApiProperty(example: 'Cyprus')]
        public string $name = '',
        #[ApiProperty(example: 'https://d3v8wzla6n4b2q.cloudfront.net/public/countries/UY.svg')]
        public string $image = '',
    ) {
    }

    public static function create(
        string $code,
        string $name,
        string $image,
    ): self {
        return new self(
            code: $code,
            name: $name,
            image: $image,
        );
    }
}
