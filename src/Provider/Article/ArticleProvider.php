<?php

namespace App\Provider\Article;

use App\ApiDTO\Response\Article\ArticleResponse;
use App\Service\ArticleService;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Exception\Http\BadRequest\MissingRequiredRequestParameterException;
use App\Exception\Http\NotFound\ObjectNotFoundHttpException;

class ArticleProvider implements ProviderInterface
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ArticleResponse
    {
        if ($operation instanceof CollectionOperationInterface) {
            throw new \RuntimeException('Not supported.');
        }

        $alias = $uriVariables['alias'] ?? null;
        if (!$alias) {
            throw new MissingRequiredRequestParameterException('Missing required request parameter - alias');
        }

        try {
            $article = $this->articleService->getArticleByAlias($alias);
        } catch (\Throwable $e) {
            throw new ObjectNotFoundHttpException($e->getMessage() ?? 'Failed to get article');
        }

        if (!$article || !$article->isActive()) {
            throw new ObjectNotFoundHttpException(sprintf('Article %s not found', $alias));
        }

        return ArticleResponse::create(
            article: $article,
        );
    }
}
