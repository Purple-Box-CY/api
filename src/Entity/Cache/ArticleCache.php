<?php

namespace App\Entity\Cache;

use App\Entity\Article;
use App\Entity\Interfaces\ArticleInterface;

class ArticleCache implements ArticleInterface
{
    public int $id;
    public string $title;
    public string $alias;
    public bool $isActive;
    public ?string $description;
    public string $createdAtFormat;
    public ?string $updatedAtFormat;

    public static function create(Article $article): self
    {
        $cache = new self();
        $cache->id = $article->getId();
        $cache->alias = $article->getAlias();
        $cache->title = $article->getTitle();
        $cache->isActive = $article->isActive();
        $cache->description = $article->getDescription();
        $cache->createdAtFormat = $article->getCreatedAtFormat();
        $cache->updatedAtFormat = $article->getUpdatedAtFormat();

        return $cache;
    }

    public static function createFromCache(object $stdObject): self
    {
        $cache = new self();
        $cache->id = $stdObject->id;
        $cache->alias = $stdObject->alias;
        $cache->title = $stdObject->title;
        $cache->isActive = $stdObject->isActive;
        $cache->description = $stdObject->description;
        $cache->createdAtFormat = $stdObject->createdAtFormat;
        $cache->updatedAtFormat = $stdObject->updatedAtFormat;

        return $cache;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAtFormat(): string
    {
        return $this->createdAtFormat;
    }

    public function getUpdatedAtFormat(): ?string
    {
        return $this->updatedAtFormat;
    }
}