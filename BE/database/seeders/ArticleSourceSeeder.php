<?php

namespace Database\Seeders;

use App\Enums\ArticleSourceEnum;
use App\Models\ArticleSource;
use Illuminate\Database\Seeder;

class ArticleSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            ArticleSourceEnum::getIdNamePair(ArticleSourceEnum::NEWS_API_ID),
            ArticleSourceEnum::getIdNamePair(ArticleSourceEnum::NYT_API_ID),
            ArticleSourceEnum::getIdNamePair(ArticleSourceEnum::GUARDIAN_API_ID),
        ];
        $this->command->getOutput()->progressStart(count($sources));
        $articleSourcesCount = ArticleSource::count();

        if (count($sources) == $articleSourcesCount) {
            $this->command->getOutput()->progressFinish();
            $this->command->getOutput()->info(sprintf('[%s] No seeding needed.', __CLASS__));
            return;
        }

        foreach ($sources as $source) {
            ArticleSource::firstOrCreate(
                [
                    'identifier' => $source['id']
                ],
                [
                    'name' => $source['name']
                ]
            );
            $this->command->getOutput()->progressAdvance();
        }
        $this->command->getOutput()->progressFinish();
    }
}
