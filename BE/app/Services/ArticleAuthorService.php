<?php

namespace App\Services;

use App\Models\Article;

class ArticleAuthorService
{
    public function getAll(){
        return Article::select('author AS name')->groupBy('author')->get();
    }
}
