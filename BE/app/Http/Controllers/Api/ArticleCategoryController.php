<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCategoryResource;
use App\Services\ArticleCategoryService;

class ArticleCategoryController extends Controller
{

    public function __construct(private readonly ArticleCategoryService $articleCategoryService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articleCategories = $this->articleCategoryService->getAll();

        return $this->returnSuccess(ArticleCategoryResource::collection($articleCategories));
    }
}
