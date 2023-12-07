<?php

namespace App\Console\Commands;

use App\DTO\ArticleItemDto;
use App\Enums\ArticleSourceEnum;
use App\Models\Article;
use App\Models\ArticleSource;
use App\Services\Article\ArticleFetcherExternal;
use App\Services\ArticleService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScrapArticles extends Command
{

    const MAX_PAGES = 10;
    const PAGE_SIZE = 100;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrap-articles';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap articles from 3 news sources(News API, The Guardian, NYT)';

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
                'fromDate' => now()->subDays(30)->startOfDay()->format('Y-m-d'),
                'toDate' => null,
                'topHeadlines' => true,
            ];
            $articleSources = ArticleSource::all();
            foreach ($articleSources as $articleSource) {
                try {
                    $categories = $articleSource->categories->pluck('name')->toArray();
                    $requestParams['categories'] = $categories;
                    $requestParams['sources'] = [$articleSource->identifier];
                    $adaptedArticles = [];
                    // If it's News API, we need to traverse on all categories of News API
                    if ($articleSource->identifier == ArticleSourceEnum::NEWS_API_ID) {
                        foreach ($categories as $category) {
                            $requestParams['categories'] = [$category];
                            $requestObject = new Request($requestParams);
                            $fetchedArticles = $this->articleService->getAll(new ArticleFetcherExternal($requestObject));
                            $adaptedArticles = array_merge($adaptedArticles,
                                $this->articleService->adaptArticlesFormat($fetchedArticles, $category));
                        }
                    } else {
                        $requestObject = new Request($requestParams);
                        $fetchedArticles = $this->articleService->getAll(new ArticleFetcherExternal($requestObject));
                        $adaptedArticles = array_merge($adaptedArticles,
                            $this->articleService->adaptArticlesFormat($fetchedArticles));
                    }

                    Log::info(sprintf('[%s] Scrapped (%d) articles from "%s" source', __CLASS__,
                        count($adaptedArticles),
                        $articleSource->identifier));

                    /** @var ArticleItemDto $articleItemDto */
                    foreach ($adaptedArticles as $articleItemDto) {
                        Article::firstOrCreate([
                            'identifier' => $articleItemDto->id,
                        ], [
                            'title' => $articleItemDto->title,
                            'excerpt' => $articleItemDto->excerpt,
                            'content_url' => $articleItemDto->contentUrl,
                            'thumbnail' => $articleItemDto->thumbnail,
                            'publish_date' => $articleItemDto->date,
                            'article_category_id' => $this->handleCategoryCreation($articleSource, $articleItemDto),
                            'author' => $articleItemDto->author,
                            'article_external_source_id' => $this->handleExternalSourceCreation($articleSource,
                                $articleItemDto),
                            'article_source_id' => $articleSource->id
                        ]);
                    }

                } catch (\Throwable $exception){
                    Log::error(sprintf('[%s:%s] - Something went wrong while attempting to scrap news articles from source "%s"', __CLASS__, __FUNCTION__, $articleSource->identifier), [
                        'exception_msg' => $exception->getMessage(),
                        'exception_trace' => $exception->getTraceAsString(),
                        'article_source_id' => $articleSource->id,
                        'request_params' => $requestParams,
                    ]);
                }
            }
        }
    }

    /**
     * @param  ArticleSource  $articleSource
     * @param  ArticleItemDto  $articleData
     * @return null
     */
    private function handleCategoryCreation(ArticleSource $articleSource, ArticleItemDto $articleData)
    {
        $articleCategoryId = null;
        if (!empty($articleData->category)) {
            $articleCategoryId = $articleSource->categories()->firstOrCreate([
                'name' => $articleData->category,
            ], [])?->id;
        }
        return $articleCategoryId;
    }

    /**
     * @param  ArticleSource  $articleSource
     * @param  ArticleItemDto  $articleData
     * @return mixed|null
     */
    private function handleExternalSourceCreation(ArticleSource $articleSource, ArticleItemDto $articleData): mixed
    {
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
}
