<?php

namespace App\Console\Commands;

use App\DTO\ArticleItemDto;
use App\Models\ArticleSource;
use App\Services\ArticleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScrapNewsSources extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrap-news-sources';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap news from 3 news sources';

    const MAX_PAGES = 10;

    const PAGE_SIZE = 100;

    public function __construct(private readonly ArticleService $articleService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $queryString = '';
        for ($pageCounter = 1; $pageCounter <= self::MAX_PAGES; $pageCounter++) {
            $requestParams = [
                'queryString' => $queryString,
                'pageSize' => self::PAGE_SIZE,
                'page' => $pageCounter,
                'fromDate' => now()->subDays(40)->startOfDay()->format('Y-m-d'),
                'toDate' => now()->endOfDay()->format('Y-m-d'),
            ];
            $articleSources = ArticleSource::all();
            foreach ($articleSources as $articleSource) {
                $categories = $articleSource->categories->pluck('name')->toArray();
                $requestParams['categories'] = $categories;
                $requestParams['queryStringWithCategories'] = $this->articleService->buildQueryStringWithCategories(
                    $queryString,
                    $categories
                );
                $fetchedArticles = $this->articleService->fetchArticles($articleSource->identifier, $requestParams);
                $adaptedArticles = $this->articleService->adaptArticlesFormat($fetchedArticles);
                Log::info(sprintf('[%s] Scrapped (%d) articles from "%s" source', __CLASS__, count($adaptedArticles), $articleSource->identifier));

                /** @var ArticleItemDto $articleItemDto */
                foreach ($adaptedArticles as $articleItemDto) {
                    $articleSource->articles()->firstOrCreate([
                        'identifier' => $articleItemDto->id,
                        'title' => $articleItemDto->title,
                    ], [
                        'excerpt' => $articleItemDto->excerpt,
                        'content_url' => $articleItemDto->contentUrl,
                        'thumbnail' => $articleItemDto->thumbnail,
                        'publish_date' => $articleItemDto->date,
                        'category' => $this->handleCategoryCreation($articleSource, $articleItemDto),
                        'author' => $articleItemDto->author,
                        'article_external_source_id' => $this->handleExternalSourceCreation($articleSource, $articleItemDto),
                    ]);
                }
            }
        }
    }

    /**
     * @param  ArticleSource  $articleSource
     * @param  ArticleItemDto  $articleData
     * @return mixed|null
     */
    private function handleExternalSourceCreation(ArticleSource $articleSource, ArticleItemDto $articleData){
        $externalSourceId = null;
        $externalSourceData = $articleData->externalSourceData;
        if (!empty($externalSourceData)) {
            $externalSourceId = $articleSource->externalSources()->firstOrCreate([
                'identifier' => $externalSourceData['id'],
            ], [
                'name' => $externalSourceData['name'],
            ])?->id;
        }
        return $externalSourceId;
    }

    /**
     * @param  ArticleSource  $articleSource
     * @param  ArticleItemDto  $articleData
     * @return null
     */
    private function handleCategoryCreation(ArticleSource $articleSource, ArticleItemDto $articleData){
        $articleCategoryId = null;
        if (!empty($articleData->category)) {
            $articleCategoryId = $articleSource->categories()->firstOrCreate([
                'name' => $articleData->category,
            ], [])?->id;
        }
        return $articleCategoryId;
    }
}
