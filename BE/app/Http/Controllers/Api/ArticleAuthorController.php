<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCategoryResource;
use App\Services\ArticleAuthorService;
use App\Services\ArticleCategoryService;

class ArticleAuthorController extends Controller
{

    public function __construct(private readonly ArticleAuthorService $articleAuthorService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articleAuthors = $this->articleAuthorService->getAll();

        return $this->returnSuccess($articleAuthors);
    }
}
