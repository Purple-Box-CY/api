<?php

namespace App\Entity;

interface MediaInterface
{
    public function getType(): ?string;
    public function getFormat(): ?string;

    public function getMediaUrl(): string;

    public function getPreviewUrl(): string;
    public function getData(): array;
}