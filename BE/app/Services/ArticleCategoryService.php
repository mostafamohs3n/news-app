<?php

namespace App\Services;

use App\Models\ArticleCategory;

class ArticleCategoryService
{

    /**
     * @return mixed
     */
    public function getAll(): mixed
    {
        return ArticleCategory::select('name')->groupBy('name')->get();
    }
}
