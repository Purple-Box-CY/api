<?php

namespace App\ApiDTO\Response\Event;

class EventDTO
{
    public function __construct(
        public string $event,
        public array $data,
    ) {
    }

    public static function create(
        string $event,
        array $data,
    ): self {
        return new self(
            event: $event,
            data: $data,
        );
    }
}