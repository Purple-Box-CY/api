<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Cache\ArticleCache;
use App\Entity\Interfaces\ArticleInterface;
use App\Repository\ArticleRepository;
use App\Service\Infrastructure\LogService;
use App\Service\Infrastructure\RedisKeys;
use App\Service\Infrastructure\RedisService;
use App\Service\Utility\MomentHelper;

class ArticleService
{

    public function __construct(
        private RedisService      $redisService,
        private ArticleRepository $articleRepository,
        private LogService        $logger,
    ) {
    }

    public function getArticleByAlias(string $alias, bool $fromCache = true): Article|ArticleInterface|null
    {
        if ($fromCache) {
            return $this->getArticleByAliasFromCache($alias);
        }

        return $this->articleRepository->findOneBy([
            'alias' => $alias,
        ]);
    }

    public function getArticleByAliasFromCache(string $alias): ?ArticleInterface
    {
        $key = sprintf(RedisKeys::KEY_ARTICLE_ITEM, $alias);

        /** @var ArticleCache $articleCache */
        $articleCache = $this->redisService->getObject($key, false);
        if ($articleCache) {
            try {
                $articleFromCache = ArticleCache::createFromCache($articleCache);
            } catch (\Exception $e) {
                $this->logger->error('Failed to create article from cache',
                    [
                        'error'                 => $e->getMessage(),
                        'alias'                 => $alias,
                        'article_data_in_cache' => $articleCache,
                    ]);

                return null;
            }

            return $articleFromCache;
        }

        try {
            $article = $this->articleRepository->findOneBy([
                'alias' => $alias,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get article by uid',
                [
                    'error' => $e->getMessage(),
                    'alias' => $alias,
                ]);
            $article = null;
        }

        if (!$article) {
            return null;
        }

        try {
            $articleCache = ArticleCache::create($article);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create article cache from article object',
                [
                    'error' => $e->getMessage(),
                    'alias' => $alias,
                ]);

            return $articleCache;
        }
        $this->redisService->setObject($key, $articleCache, MomentHelper::SECONDS_MONTH, false);

        return $articleCache;
    }
}
