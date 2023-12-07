<?php

namespace App\Services;

use App\Enums\ApiEndpointEnum;
use App\Enums\ArticleSourceEnum;
use App\Models\ArticleExternalSource;
use App\Models\ArticleSource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ArticleSourceService
{

    /**
     * @return mixed
     */
    public function getAll()
    {
        $articleSources = ArticleSource::whereDoesntHave('externalSources')->get();
        $articleExternalSources = ArticleExternalSource::all();

        return $articleSources->concat($articleExternalSources);
    }

    /**
     * @return array
     */
    public function fetchNewsApiSources(): array
    {
        $sources = [];
        $response = Http::get(env('NEWS_API_URL').ApiEndpointEnum::NEWS_API_SOURCES, [
            'apiKey' => env('NEWS_API_KEY')
        ]);
        $responseArray = $response->json();
        if ($response->status() === Response::HTTP_OK) {
            $newsApiSources = Arr::get($responseArray, 'sources', []);
            $newsApiSources = array_map(function ($source) {
                return array_merge(
                    Arr::only($source, ['id', 'name']),
                    ['src' => ArticleSourceEnum::getIdNamePair(ArticleSourceEnum::NEWS_API_ID)]
                );
            }, $newsApiSources);
            $sources = $newsApiSources;
        }

        return $sources;
    }
}
