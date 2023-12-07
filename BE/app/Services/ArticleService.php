<?php

namespace App\Services;

use App\Adapters\ArticleItemAdapter;
use App\Interfaces\ArticleFetcher;

class ArticleService
{
    public function getAll(ArticleFetcher $articleFetcher)
    {
        return $articleFetcher->getAll();
    }

    /**
     * @param  array  $articles
     * @param  string|null  $assignedCategory
     * @return array
     */
    public function adaptArticlesFormat(array $articles, string $assignedCategory = null): array
    {
        return array_values(array_filter(array_map(function ($article) use ($assignedCategory) {
            if (!empty($assignedCategory)) {
                $article['category'] = $assignedCategory;
            }
            $adapter = new ArticleItemAdapter($article);
            if (in_array($adapter->getTitle(), [null, '', '[Removed]'])) {
                return null;
            }
            return $adapter->toDto();
        }, $articles)));
    }

    /**
     * @param  array  $articles
     * @return void
     */
    public function sortArticles(array &$articles): void
    {
        usort($articles, function ($a, $b) {
            return strtotime(is_array($b) ? $b['date'] : $b->date)
                <=>
                strtotime(is_array($a) ? $a['date'] : $a->date);
        });
    }

    /**
     * @param  string  $queryString
     * @param  array  $categories
     * @return string
     */
    public function buildQueryStringWithCategories(string $queryString, array $categories = []): string
    {
        $categoriesString = implode(' OR ', $categories);
        if (empty($queryString)) {
            return $categoriesString;
        }
        return sprintf(
            '%s%s',
            $queryString,
            !empty($categories)
                ? sprintf(' AND (%s)', implode(' OR ', $categories))
                : ''
        );
    }
}
