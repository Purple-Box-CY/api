<?php

namespace App\ApiDTO\Response\Marker;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Marker;

class ResponseMarkerInfo
{
    public function __construct(
        #[ApiProperty(example: '01HDRC59DGYS83QYCR767Z9KVV')]
        public string $uid,

        #[ApiProperty(example: 'paper')]
        public string $type,

        #[ApiProperty(example: 'paper box')]
        public ?string $name,

        #[ApiProperty(example: 'Best paper box')]
        public ?string $description,

        #[ApiProperty(example: 'https://api.challengerhub.pro/api/public/uploads/preview/01he51ce48ckawev095qjsqw4g.png')]
        public ?string $imageUrl,

        public MarkerLocation $location,

    ) {
    }

    public static function create(Marker $marker): self
    {
        return new self(
            uid: $marker->getUid(),
            type: $marker->getType(),
            name: $marker->getName(),
            description: $marker->getDescription(),
            imageUrl: $marker->getImageUrl(),
            location: new MarkerLocation($marker->getLat(), $marker->getLng()),
        );
    }
}
