<?php

namespace App\ApiDTO\Response\Marker;

use ApiPlatform\Metadata\ApiProperty;

class ResponseMarkerList
{
    public function __construct(
        #[ApiProperty(example: 15)]
        public int   $count,
        /** @var ResponseMarkerShort[] */
        public array $items = [],
    ) {
    }

    /**
     * @param ResponseMarkerShort[] $markers
     *
     * @return self
     */
    public static function create(array $markers): self
    {
        return new self(
            count: count($markers),
            items: $markers,
        );
    }
}
