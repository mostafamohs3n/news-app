<?php

namespace App\Services\Article;

use App\Interfaces\ArticleFetcher;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleFetcherDatabase implements ArticleFetcher
{

    public function __construct(private readonly Request $request){
    }

    public function getAll(): mixed
    {
        $request = $this->request;
        $categories = $request->get('categories', []);
        $sources = $request->get('sources', []);
        $externalSources = $request->get('external_sources', []);
        $authors = $request->get('authors', []);
        $queryString = $request->get('q', '');
        $pageSize = 10;
        $page = $request->get('page', 1);
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        return Article::search($queryString)
                      ->fromDate($fromDate)
                      ->toDate($toDate)
                      ->categories($categories, $sources)
                      ->sources($sources)
                      ->externalSources($externalSources)
                      ->authors($authors)
                      ->latest('publish_date')
                      ->paginate($pageSize)
        ;
    }
}
