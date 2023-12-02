<?php

namespace Database\Seeders;

use App\Enums\ArticleSourceEnum;
use App\Models\ArticleExternalSource;
use App\Models\ArticleSource;
use App\Services\ArticleSourceService;
use Illuminate\Database\Seeder;

class ArticleExternalSourceSeeder extends Seeder
{

    public function __construct(private readonly ArticleSourceService $articleSourceService)
    {
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $newsApiExternalSources = $this->articleSourceService->fetchNewsApiSources();
        $newsApiSource = ArticleSource::whereIdentifier(ArticleSourceEnum::NEWS_API_ID)->first();
        if(!$newsApiSource){
            $this->command->getOutput()->warning(sprintf('[%s] "%s" source does not exist, please run the ArticleSourceSeeder first', __CLASS__, ArticleSourceEnum::NEWS_API_ID));
            return;
        }
        if(empty($newsApiExternalSources)){
            $this->command->getOutput()->info(sprintf('[%s] No News Api External Sources fetched, please check your API key.', __CLASS__));
        }
        $this->command->getOutput()->progressStart(count($newsApiExternalSources));
        $externalSourcesCount = ArticleExternalSource::count();
        if(count($newsApiExternalSources) == $externalSourcesCount){
            $this->command->getOutput()->progressFinish();
            $this->command->getOutput()->info(sprintf('[%s] No seeding needed.', __CLASS__));
            return;
        }

        foreach ($newsApiExternalSources as $externalSource){
            ArticleExternalSource::firstOrCreate([
                'identifier' => $externalSource['id'],
                'name' => $externalSource['name'],
                'article_source_id' => $newsApiSource->id,
            ], []);
            $this->command->getOutput()->progressAdvance();
        }
        $this->command->getOutput()->progressFinish();

    }
}
