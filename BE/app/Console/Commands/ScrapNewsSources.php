<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\ArticleCategory;
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

    public function __construct(private readonly ArticleService $articleService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // run news scrapping for news that are in the past 24 hours only.
        // for three sources, each source with their own category.

        $queryString = '';
        $categories = []; // list of categories per this news source
        $newsApiSources = []; // will check if needed.
        $fromDate = now()->subDays(50)->startOfDay()->format('Y-m-d');
        $fromDate = null;
        $pageSize = 1;
        $requestParams = [
            'queryString' => $queryString,
            'queryStringWithCategories' => sprintf('%s%s', $queryString,
                !empty($categories) ? sprintf(' AND (%s)', implode(' OR ', $categories)) : ''),
            'categories' => $categories,
            'sources' => array_filter($newsApiSources),
            'pageSize' => $pageSize,
            'page' => 1,
            'fromDate' => $fromDate,
            'toDate' => null,
        ];

        //@TODO: Or 100 per category?!

        $articleSources = ArticleSource::all();
        foreach ($articleSources as $articleSource) {
            $categories = $articleSource->categories->pluck('name')->toArray();
            $requestParams['categories'] = $categories;
            $requestParams['queryStringWithCategories'] = $this->articleService->buildQueryStringWithCategories($queryString,
                $categories);
//            var_dump($requestParams);
//            var_dump($requestParams['categories']);
            $this->output->info(sprintf('[%s] Scrapping news for "%s" source', __CLASS__, $articleSource->identifier));
            $fetchedArticles = $this->articleService->fetchArticles($articleSource->identifier, $requestParams);
            $adaptedArticles = $this->articleService->adaptArticlesFormat($fetchedArticles);

            foreach ($adaptedArticles as $article) {
                // if external source doesnt exist, create it.
                $externalSourceData = $article['externalSourceData'];
                $externalSource = null;
                if(!empty($externalSourceData)) {
                    $externalSource = $articleSource->externalSources()->firstOrCreate([
                        'identifier' => $externalSourceData['id'],
                    ], [
                        'name' => $externalSourceData['name'],
                    ])?->id;
                }

                // if category doesnt exist, create it.
                $articleCategory = null;
                if(!empty($article['category'])){
                    $articleCategory = $articleSource->categories()->firstOrCreate([
                        'name' => $article['category'],
                    ], [])?->id;
                }

                // create article
                $articleModel = $articleSource->articles()->firstOrCreate([
                    'identifier' => $article['id'],
                    'title' => $article['title'],
                ], [
                    'excerpt' => $article['excerpt'],
                    'content_url' => $article['contentUrl'],
                    'thumbnail' => $article['thumbnail'],
                    'publish_date' => $article['date'],
                    'category' => $articleCategory,
                    'author' => $article['author'],
                    'article_external_source_id' => $externalSource,
                ]);

            }
            var_dump("___________________________________________");
            var_dump("___________________________________________");
            var_dump($adaptedArticles);
        }
        $this->output->info('Cron running!');
        Log::info('Cron running!');
        var_dump("LOGGING!");
//        return Command::SUCCESS;
        // Scrap from all news sources for at least 2 months old
        // For all available categories by every news source
        // Save them in database
        // Run this in chunks
        // Cron this as a schedule
    }
}
