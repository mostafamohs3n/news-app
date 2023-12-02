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
        $url = $this->getApiEndpointBySource($sourceId, $requestParams['queryString']);
        $request = Http::get($url, $this->getRequestParams($sourceId, $requestParams));
        $response = $request->json();
        if (ResponseUtilities::isResponseError($response)) {
            $errorData = ResponseUtilities::getResponseErrorData($response);
            Log::info(sprintf('[%s] [Source=%s] %s: %s', __CLASS__, $sourceId, $errorData['code'], $errorData['message']));
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
     * @param $source
     * @param  string  $queryString
     * @return string
     */
    private function getApiEndpointBySource($source, $queryString = ''): string
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
    private function getRequestParams($source, $requestParams): array
    {
        $newsApiSources = array_filter(array_diff($requestParams['sources'],
            [ArticleSourceEnum::GUARDIAN_API_ID, ArticleSourceEnum::NYT_API_ID]));

        return array_filter(match ($source) {
            ArticleSourceEnum::NEWS_API_ID =>
            [
                'apiKey' => env('NEWS_API_KEY'),
                'q' => urlencode(empty($requestParams['categories']) ? $requestParams['queryString'] : $requestParams['queryStringWithCategories']),
                'sources' => !empty($newsApiSources) ? implode(',', $newsApiSources) : null,
                'from' => $requestParams['fromDate'] ?: null, //format: YYYY-MM-DD
                'to' => $requestParams['toDate'] ?: null, //format: YYYY-MM-DD
                'page' => $requestParams['page'],
                'pageSize' => $requestParams['pageSize'],
                'language' => 'en',
            ],
            ArticleSourceEnum::GUARDIAN_API_ID => [
                'api-key' => env('GUARDIAN_API_KEY'),
                'q' => $requestParams['queryString'],
                'section' => !empty($requestParams['categories']) ? implode('|', $requestParams['categories']) : null,
                'from-date' => $requestParams['fromDate'] ?: null,
                'to-date' => $requestParams['toDate'] ?: null,
                'page' => $requestParams['page'],
                'page-size' => $requestParams['pageSize'],
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
                'begin_date' => str_replace('-', '', $requestParams['fromDate']) ?: null, //format: YYYYMMDD
                'end_date' => str_replace('-', '', $requestParams['toDate']) ?: null, //format: YYYYMMDD
                'page' => $requestParams['page'],
                'fl' => 'headline,multimedia,web_url,snippet,lead_paragraph,source,abstract,pub_date,news_desk,section_name,byline,_id',
                'sort' => 'newest',
            ],
            default => $requestParams,
        });
    }

    public function adaptArticlesFormat($articles)
    {
        $mergedNews = [];

        foreach ($articles as $article) {
            $adapter = new ArticleItemAdapter($article);
            $mergedNews[] = $adapter->adapt();
        }

        // sort by date
        //@TODO: Might not be needed
        usort($mergedNews, function ($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });

        return $mergedNews;
    }

    public function buildQueryStringWithCategories($queryString, $categories = [])
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
