<?php

namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation;
use App\ApiDTO\Response\Article\ArticleResponse;
use App\Provider\Article\ArticleProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[ApiResource(
    shortName: 'Articles',
    operations: [
        new Get(
            uriTemplate: '/articles/{alias}',
            openapi: new Operation(
                summary: 'Article',
                description: 'Article by alias',
            ),
            output: ArticleResponse::class,
            provider: ArticleProvider::class,
        ),
    ],
)]
class ArticleController extends AbstractController
{
}
