<?php

namespace App\ApiDTO\Response\Article;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Interfaces\ArticleInterface;

class ArticleResponse
{
    public function __construct(
        #[ApiProperty(example: 'terms_of_service')]
        public string $alias,

        #[ApiProperty(example: 'Terms of service')]
        public string $title,

        #[ApiProperty(example: 'A terms of service sets all user rules, restrictions, and prohibited behaviors, and outlines your company’s liability limitations, property rights, and dispute resolutions.')]
        public ?string $description,

        #[ApiProperty(example: '2023-10-01 10:00:00')]
        public ?string $createdAt,

        #[ApiProperty(example: '2023-10-31 23:50:00')]
        public ?string $updatedAt,
    ) {
    }

    public static function create(
        ArticleInterface $article,
    ): self {
        return new self(
            alias: $article->getAlias(),
            title: $article->getTitle(),
            description: preg_replace("/\r\n|\r|\n/", '<br/>', (string)$article->getDescription()),
            createdAt: $article->getCreatedAtFormat(),
            updatedAt: $article->getUpdatedAtFormat(),
        );
    }
}
