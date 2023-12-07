<?php

namespace App\Http\Controllers\Api;

use App\Enums\ArticleSourceEnum;
use App\Helpers\Utilities;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Services\Article\ArticleFetcherDatabase;
use App\Services\Article\ArticleFetcherExternal;
use App\Services\ArticleService;
use App\Services\ArticleSourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService
    ) {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $articles = $this->articleService->getAll(new ArticleFetcherDatabase($request));

        // If db query returned empty results, then query the external API directly
        if (count($articles)) {
            $paginatedArticles = (ArticleResource::collection($articles))->response()->getContent();
            $articles = json_decode($paginatedArticles, true);
        } else {
            //@TODO: Determine if we should save the records directly upon fetching or set up a job to do so.
            $articles = $this->articleService->getAll(new ArticleFetcherExternal($request));
            $articles = $this->articleService->adaptArticlesFormat($articles);
            $articles = ArticleResource::collection($articles);
        }

        return $this->returnSuccess($articles);
    }
}
