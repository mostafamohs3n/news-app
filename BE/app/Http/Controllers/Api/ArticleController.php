<?php

namespace App\Http\Controllers\Api;

use App\Enums\ArticleSourceEnum;
use App\Helpers\Utilities;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
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
        $categories = $request->get('categories', []);
        $sources = $request->get('sources', []);
        $externalSources = $request->get('external_sources', []);
        $queryString = $request->get('q', '');
        $pageSize = 10;
        $page = $request->get('page', 1);
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        //@TODO: Move to service class
        $articles = Article::search($queryString)
                           ->fromDate($fromDate)
                           ->toDate($toDate)
                           ->categories($categories, $sources)
                           ->sources($sources)
                           ->externalSources($externalSources)
                           ->latest('publish_date')
                           ->paginate($pageSize)
        ;


        return $this->returnSuccess(
            json_decode((ArticleResource::collection($articles))->response()->getContent(), true)
        );
        //@TODO: Replace with querying the database.


        //@TODO: If db query returned empty, then query the external API directly
        //@TODO: Determine if we should save the records directly or set up a job to do so.
        //@TODO: Check how we can work with two article resources or one that handles both cases.
        /*
        $cacheKey = Utilities::getArticlesCacheKey($requestParams);
        $articles = Cache::remember($cacheKey, 3600, function () use ($requestParams, $sources) {
            $mergedArticles = [];
            if ($this->sourceService->isApplicableSource(ArticleSourceEnum::NEWS_API_ID, $sources)) {
                $newsApiResponse = $this->articleService->fetchArticles(ArticleSourceEnum::NEWS_API_ID, $requestParams);
                $mergedArticles = array_merge($mergedArticles, $newsApiResponse);
            }
            if ($this->sourceService->isApplicableSource(ArticleSourceEnum::GUARDIAN_API_ID, $sources)) {
                $guardianApiResponse = $this->articleService->fetchArticles(ArticleSourceEnum::GUARDIAN_API_ID, $requestParams);
                $mergedArticles = array_merge($mergedArticles, $guardianApiResponse);
            }
            if ($this->sourceService->isApplicableSource(ArticleSourceEnum::NYT_API_ID, $sources)) {
                $nytApiResponse = $this->articleService->fetchArticles(ArticleSourceEnum::NYT_API_ID, $requestParams);
                $mergedArticles = array_merge($mergedArticles, $nytApiResponse);
            }
            $articles = $this->articleService->adaptArticlesFormat($mergedArticles);
            $this->articleService->sortArticles($articles);

            return $articles;
        });
        */

//        return $this->returnSuccess(ArticleResource::collection($articles));
    }

    //@TODO: Move away
    public function indexOld(Request $request)
    {
        $categories = $request->get('categories', []) ?? [];
        $sources = $request->get('sources', []) ?? [];
        //@TODO: Replace with querying the database.

        //@TODO: If db query returned empty, then query the external API directly
        //@TODO: Determine if we should save the records directly or set up a job to do so.
        //@TODO: Check how we can work with two article resources or one that handles both cases.
        $requestParams = [
            'queryString' => $request->get('q', ''),
            'queryStringWithCategories' => $this->articleService->buildQueryStringWithCategories((string)$request->get('q',
                ''), $categories),
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
                $guardianApiResponse = $this->articleService->fetchArticles(ArticleSourceEnum::GUARDIAN_API_ID,
                    $requestParams);
                $mergedArticles = array_merge($mergedArticles, $guardianApiResponse);
            }
            if ($this->sourceService->isApplicableSource(ArticleSourceEnum::NYT_API_ID, $sources)) {
                $nytApiResponse = $this->articleService->fetchArticles(ArticleSourceEnum::NYT_API_ID, $requestParams);
                $mergedArticles = array_merge($mergedArticles, $nytApiResponse);
            }
            $articles = $this->articleService->adaptArticlesFormat($mergedArticles);
            $this->articleService->sortArticles($articles);

            return $articles;
        });

        return $this->returnSuccess(ArticleResource::collection($articles));
    }

}
