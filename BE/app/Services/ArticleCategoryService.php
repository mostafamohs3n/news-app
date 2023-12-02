<?php

namespace App\Services;

use App\Models\ArticleCategory;

class ArticleCategoryService
{

    public function getCategories()
    {
        return ArticleCategory::select('name')->groupBy('name')->get();
    }
}
