<?php

namespace App\Http\Controllers\Api;

use App\Enums\ArticleSourceEnum;
use App\Helpers\Utilities;
use App\Http\Controllers\Controller;
use App\Services\ArticleService;
use App\Services\ArticleSourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService,
        private readonly ArticleSourceService $sourceService
    ) {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = $request->get('categories', []) ?? [];
        $sources = $request->get('sources', []) ?? [];
        $requestParams = [
            'queryString' => $request->get('q'),
            'queryStringWithCategories' => $this->articleService->buildQueryStringWithCategories($request->get('q'), $categories),
            'categories' => $categories ?? [],
            'sources' => array_filter($request->get('sources', [])),
            'pageSize' => 10,
            'page' => $request->get('page', 1),
            'fromDate' => $request->get('from_date'),
            'toDate' => $request->get('to_date'),
        ];
        $cacheKey = Utilities::getArticlesCacheKey($requestParams);
        $articles = Cache::remember($cacheKey, 3600, function () use ($requestParams, $sources) {
            $mergedArticles = [];
            if ($this->sourceService->isApplicableSource(ArticleSourceEnum::NEWS_API_ID, $sources)) {
                $newsApiResponse = $this->articleService->fetchArticles(ArticleSourceEnum::NEWS_API_ID, $requestParams);
                $mergedArticles = array_merge($mergedArticles, $newsApiResponse);
            }
            if ($this->sourceService->isApplicableSource(ArticleSourceEnum::GUARDIAN_API_ID, $sources)) {
//                $guardianApiResponse = $this->articleService->fetchArticles(ArticleSourceEnum::GUARDIAN_API_ID, $requestParams);
//                $mergedArticles = array_merge($mergedArticles, $guardianApiResponse);
            }
            if ($this->sourceService->isApplicableSource(ArticleSourceEnum::NYT_API_ID, $sources)) {
//                $nytApiResponse = $this->articleService->fetchArticles(ArticleSourceEnum::NYT_API_ID, $requestParams);
//                $mergedArticles = array_merge($mergedArticles, $nytApiResponse);
            }
            return $this->articleService->adaptArticlesFormat($mergedArticles);
        });

        return $this->returnSuccess(['requestParams' => $requestParams, 'articles' => $articles]);
    }
}
