<?php

namespace App\ApiDTO\Response\Marker;

use ApiPlatform\Metadata\ApiProperty;

class MarkerLocation
{
    public function __construct(
        #[ApiProperty(example: 34.79006094745252)]
        public float $lat,

        #[ApiProperty(example: 32.418863693095325)]
        public ?int $lng,

    ) {
    }
}
