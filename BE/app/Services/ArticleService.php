<?php

namespace App\Services;

use App\Adapters\ArticleItemAdapter;
use App\Enums\ApiEndpointEnum;
use App\Enums\ArticleSourceEnum;
use App\Helpers\ResponseUtilities;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArticleService
{
    /**
     * @param  string  $sourceId
     * @param  array  $requestParams
     * @return array
     */
    public function fetchArticles(string $sourceId, array $requestParams): array
    {
        $url = $this->getApiEndpointBySource($sourceId);
        $request = Http::get($url, $this->getFormattedRequestParams($sourceId, $requestParams));
        $response = $request->json();
        if (ResponseUtilities::isResponseError($response)) {
            $errorData = ResponseUtilities::getResponseErrorData($response);
            Log::info(sprintf('[%s] [Source=%s] %s: %s', __CLASS__, $sourceId, $errorData['code'],
                $errorData['message']));
            return [];
        }
        return match ($sourceId) {
            ArticleSourceEnum::NEWS_API_ID => Arr::get($response, 'articles', []),
            ArticleSourceEnum::NYT_API_ID => Arr::get(Arr::get($response, 'response', []), 'docs', []),
            ArticleSourceEnum::GUARDIAN_API_ID => Arr::get(Arr::get($response, 'response'), 'results', []),
            default => $response
        };
    }

    /**
     * @param  string  $source
     * @return string
     */
    private function getApiEndpointBySource(string $source): string
    {
        $sourceUrlEndpointMapper = [
            ArticleSourceEnum::NEWS_API_ID => env('NEWS_API_URL').ApiEndpointEnum::NEWS_API_LISTING,
            ArticleSourceEnum::GUARDIAN_API_ID => env('GUARDIAN_API_URL').ApiEndpointEnum::GUARDIAN_LISTING,
            ArticleSourceEnum::NYT_API_ID => env('NYT_API_URL').ApiEndpointEnum::NYT_LISTING
        ];
        $url = $sourceUrlEndpointMapper[$source] ?? null;
        if (!$url) {
            throw new \LogicException('Source provided is not valid.');
        }
        return $url;
    }

    /**
     * @param $source
     * @param $requestParams
     * @return array
     */
    private function getFormattedRequestParams($source, $requestParams): array
    {
        $newsApiSources = array_filter(array_diff($requestParams['sources'] ?? [],
            [ArticleSourceEnum::GUARDIAN_API_ID, ArticleSourceEnum::NYT_API_ID]));

        return array_filter(match ($source) {
            ArticleSourceEnum::NEWS_API_ID =>
            [
                'apiKey' => env('NEWS_API_KEY'),
                'q' => urlencode(empty($requestParams['categories']) ? $requestParams['queryString'] : $requestParams['queryStringWithCategories']),
                'sources' => !empty($newsApiSources) ? implode(',', $newsApiSources) : null,
                'from' => $requestParams['fromDate'] ?? null, //format: YYYY-MM-DD
                'to' => $requestParams['toDate'] ?? null, //format: YYYY-MM-DD
                'page' => $requestParams['page'],
                'pageSize' => min(100, $requestParams['pageSize']),
                'language' => 'en',
            ],
            ArticleSourceEnum::GUARDIAN_API_ID => [
                'api-key' => env('GUARDIAN_API_KEY'),
                'q' => $requestParams['queryString'],
                'section' => !empty($requestParams['categories']) ? implode('|', $requestParams['categories']) : null,
                'from-date' => $requestParams['fromDate'] ?? null,
                'to-date' => $requestParams['toDate'] ?? null,
                'page' => $requestParams['page'],
                'page-size' => min(50, $requestParams['pageSize']),
                'show-fields' => 'body,thumbnail,trailText,byline',
                'order-by' => 'newest',
                'format' => 'json',
            ],
            ArticleSourceEnum::NYT_API_ID => [
                'api-key' => env('NYT_API_KEY'),
                'q' => $requestParams['queryString'],
                'fq' => !empty($requestParams['categories'])
                    ? sprintf('section_name:("%s")', implode('","', $requestParams['categories']))
                    : null,
                'begin_date' => str_replace('-', '', $requestParams['fromDate'] ?? '') ?: null, //format: YYYYMMDD
                'end_date' => str_replace('-', '', $requestParams['toDate'] ?? '') ?: null, //format: YYYYMMDD
                'page' => $requestParams['page'],
                'fl' => 'headline,multimedia,web_url,snippet,lead_paragraph,source,abstract,pub_date,news_desk,section_name,byline,_id',
                'sort' => 'newest',
            ],
            default => $requestParams,
        });
    }

    /**
     * @param  array  $articles
     * @return array
     */
    public function adaptArticlesFormat(array $articles): array
    {
        return array_map(function ($article) {
            $adapter = new ArticleItemAdapter($article);
            return $adapter->toDto();
        }, $articles);
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
