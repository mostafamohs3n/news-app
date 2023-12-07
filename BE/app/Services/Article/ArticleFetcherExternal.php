<?php

namespace App\Services\Article;

use App\Enums\ApiEndpointEnum;
use App\Enums\ArticleSourceEnum;
use App\Helpers\ResponseUtilities;
use App\Helpers\Utilities;
use App\Interfaces\ArticleFetcher;
use App\Models\ArticleExternalSource;
use App\Models\ArticleSource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArticleFetcherExternal implements ArticleFetcher
{

    public function __construct(private readonly Request $request)
    {

    }

    public function getAll(): array
    {
        $categories = $this->request->get('categories', []) ?? [];
        $sources = $this->request->get('sources', []);
        $newsApiSources = $this->request->get('external_sources', []);

        // switch ids into identifiers to be used in the external APIs
        $sources = array_values(array_filter(array_reduce($sources, function($carry, $source){
            $carry[] = ArticleSource::find($source)?->identifier ?? null;
            return $carry;
        }, [])));

        $newsApiSources = array_values(array_filter(array_reduce($newsApiSources, function($carry, $source){
            $carry[] = ArticleExternalSource::find($source)?->identifier ?? null;
            return $carry;
        }, [])));
        $sources = [...$sources, ...$newsApiSources];

        $categories = array_reduce($categories, function ($carry, $category){
            $carry[] = strtolower($category);
            return $carry;
        }, []);


        $requestParams = [
            'queryString' => $this->request->get('q'),
            'queryStringWithCategories' => sprintf('%s AND (%s)', $this->request->get('q'),
                implode(' OR ', $categories)),
            'categories' => $categories ?? [],
            'sources' => array_filter($sources),
            'pageSize' => 10,
            'page' => $this->request->get('page', 1),
            'fromDate' => $this->request->get('from_date'),
            'toDate' => $this->request->get('to_date'),
            'topHeadlines' => false,
        ];

        $cacheKey = Utilities::getArticlesCacheKey($requestParams);
        return Cache::remember($cacheKey, 3600, function () use ($requestParams, $sources) {
            $mergedArticles = [];
            if ($this->isApplicableSource(ArticleSourceEnum::NEWS_API_ID, $sources)) {
                $newsApiResponse = $this->fetchSourceArticles(ArticleSourceEnum::NEWS_API_ID, $requestParams);
                $mergedArticles = array_merge($mergedArticles, $newsApiResponse);
            }
            if ($this->isApplicableSource(ArticleSourceEnum::GUARDIAN_API_ID, $sources)) {
                $guardianApiResponse = $this->fetchSourceArticles(ArticleSourceEnum::GUARDIAN_API_ID,
                    $requestParams);
                $mergedArticles = array_merge($mergedArticles, $guardianApiResponse);
            }
            if ($this->isApplicableSource(ArticleSourceEnum::NYT_API_ID, $sources)) {
                $nytApiResponse = $this->fetchSourceArticles(ArticleSourceEnum::NYT_API_ID, $requestParams);
                $mergedArticles = array_merge($mergedArticles, $nytApiResponse);
            }
            return $mergedArticles;
        });
    }

    /**
     * @param $sourceId
     * @param $sources
     * @return bool
     */
    private function isApplicableSource($sourceId, $sources): bool
    {
        if (empty($sources)) {
            return true;
        }
        if ($sourceId == ArticleSourceEnum::NEWS_API_ID) {
            return (!empty(array_diff($sources, [ArticleSourceEnum::GUARDIAN_API_ID, ArticleSourceEnum::NYT_API_ID])));
        } else {
            return in_array($sourceId, $sources);
        }
    }

    /**
     * @param  string  $sourceId
     * @param  array  $requestParams
     * @return array
     */
    public function fetchSourceArticles(string $sourceId, array $requestParams): array
    {
        $url = $this->getApiEndpointBySource($sourceId, $requestParams['topHeadlines'] ?? false);
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
     * @param  bool  $topHeadlines
     * @return string
     */
    private
    function getApiEndpointBySource(
        string $source,
        bool $topHeadlines = false
    ): string {
        $sourceUrlEndpointMapper = [
            ArticleSourceEnum::NEWS_API_ID => env('NEWS_API_URL').($topHeadlines ? ApiEndpointEnum::NEWS_API_TOP_HEADLINES : ApiEndpointEnum::NEWS_API_LISTING),
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
     * @param  string  $source
     * @param  array  $requestParams
     * @return array
     */
    private
    function getFormattedRequestParams(
        string $source,
        array $requestParams
    ): array {
        $newsApiSources = array_filter(array_diff($requestParams['sources'] ?? [],
            [ArticleSourceEnum::GUARDIAN_API_ID, ArticleSourceEnum::NYT_API_ID]));

        return array_filter(match ($source) {
            ArticleSourceEnum::NEWS_API_ID =>
            array_merge(
                [
                    'apiKey' => env('NEWS_API_KEY'),
                    'page' => $requestParams['page'],
                    'pageSize' => min(100, $requestParams['pageSize']),
                ],
                $requestParams['topHeadlines'] ?
                    [
                        'category' => $requestParams['categories'][0] ?? null,
                        'country' => 'us',
                        'q' => urlencode($requestParams['queryString']),
                    ]
                    : [
                    'sources' => !empty($newsApiSources) ? implode(',', $newsApiSources) : null,
                    'from' => $requestParams['fromDate'] ?? null, //format: YYYY-MM-DD
                    'to' => $requestParams['toDate'] ?? null, //format: YYYY-MM-DD
                    'language' => 'en',
                    'q' => urlencode(empty($requestParams['categories']) ? $requestParams['queryString'] : $requestParams['queryStringWithCategories']),
                ]),
            ArticleSourceEnum::GUARDIAN_API_ID => [
                'api-key' => env('GUARDIAN_API_KEY'),
                'q' => $requestParams['queryString'],
                'section' => !empty($requestParams['categories']) ? implode('|',
                    $requestParams['categories']) : null,
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


}
