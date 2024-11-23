<?php

namespace App\Entity\Interfaces;

interface ArticleInterface
{
    public function getId(): ?int;
    public function getTitle(): ?string;
    public function getAlias(): ?string;
    public function getDescription(): ?string;
    public function isActive(): ?bool;
    public function getCreatedAtFormat(): ?string;
    public function getUpdatedAtFormat(): ?string;

}