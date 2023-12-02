<?php

namespace Database\Seeders;

use App\Enums\ArticleCategoryEnum;
use App\Enums\ArticleSourceEnum;
use App\Models\ArticleCategory;
use App\Models\ArticleSource;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ArticleSourceEnum::NEWS_API_ID => ArticleCategoryEnum::NEWS_API_CATEGORIES,
            ArticleSourceEnum::NYT_API_ID => ArticleCategoryEnum::NYT_CATEGORIES,
            ArticleSourceEnum::GUARDIAN_API_ID => ArticleCategoryEnum::GUARDIAN_CATEGORIES,
        ];
        $categoriesTotalCount = array_sum(array_map(function($categoryArray){
            return count($categoryArray);
        }, array_values($categories)));

        $this->command->getOutput()->progressStart($categoriesTotalCount);
        $articleCategoriesCount = ArticleCategory::count();

        if ($articleCategoriesCount == $categoriesTotalCount) {
            $this->command->getOutput()->progressFinish();
            $this->command->getOutput()->info(sprintf('[%s] No seeding needed.', __CLASS__));
            return;
        }
        foreach ($categories as $source => $categoriesList){
            $sourceModel = ArticleSource::whereIdentifier($source)->first();
            if(!$sourceModel){
                $this->command->getOutput()->warning(sprintf('[%s] "%s" source does not exist, please run the ArticleSourceSeeder first', __CLASS__, $source));
                continue;
            }
            foreach ($categoriesList as $category){
                ArticleCategory::firstOrCreate(
                    [
                        'name' => $category,
                        'article_source_id' => $sourceModel->id
                    ],
                    [
                    ]
                );
            }
            $this->command->getOutput()->progressAdvance(count($categoriesList));
            $this->command->getOutput()->info(sprintf('[%s] Seeded %d categories for "%s" source.', __CLASS__, count($categoriesList), $source));
        }
        $this->command->getOutput()->progressFinish();
    }
}
