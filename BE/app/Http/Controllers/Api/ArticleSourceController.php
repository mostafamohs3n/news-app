<?php

namespace App\Http\Controllers\Api;

use App\Enums\ArticleSourceEnum;
use App\Enums\CacheKeyEnum;
use App\Enums\CacheTtlEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleSourceResource;
use App\Services\ArticleSourceService;
use Illuminate\Support\Facades\Cache;

class ArticleSourceController extends Controller
{
    public function __construct(private readonly ArticleSourceService $articleSourceService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $cacheKey = CacheKeyEnum::SOURCES;
        $sourcesList = Cache::remember($cacheKey, CacheTtlEnum::TTL_1_DAY, function () {
            return $this->articleSourceService->getSourcesList();
        });

        return $this->returnSuccess(ArticleSourceResource::collection($sourcesList));
    }
}
