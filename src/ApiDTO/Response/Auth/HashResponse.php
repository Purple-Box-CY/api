<?php

namespace App\ApiDTO\Response\Auth;

use ApiPlatform\Metadata\ApiProperty;

class HashResponse
{
    public function __construct(
        #[ApiProperty(example: 'eyJpYXQiOjE2OTkyNTg5MjcsImV4cCI6MTY')]
        public string $hash,
    ) {
    }
}
