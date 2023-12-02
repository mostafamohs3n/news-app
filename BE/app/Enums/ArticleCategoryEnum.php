<?php

namespace App\Enums;

class ArticleCategoryEnum
{
    public const NEWS_API_CATEGORIES = [
        'business',
        'entertainment',
        'general',
        'health',
        'science',
        'sports',
        'technology'
    ];

    public const NYT_CATEGORIES = [
        'Arts',
        'Automobiles',
        'Autos',
        'Blogs',
        'Books',
        'Booming',
        'Business',
        'Corrections',
        'Education',
        'Fashion & Style',
        'Food',
        'Front Page',
        'Giving',
        'Global Home',
        'Great Homes and Destinations',
        'Health',
        'Home and Garden',
        'International Home',
        'Job Market',
        'Learning',
        'Magazine',
        'Movies',
        'Multimedia',
        'NYT Now',
        'National',
        'New York',
        'New York and Region',
        'Obituaries',
        'Olympics',
        'Open',
        'Opinion',
        'Paid Death Notices',
        'Public Editor',
        'Real Estate',
        'Science',
        'Sports',
        'Style',
        'Sunday Magazine',
        'Sunday Review',
        'T Magazine',
        'Technology',
        'The Public Editor',
        'The Upshot',
        'Theater',
        'Times Topics',
        'TimesMachine',
        'Today\'s Headlines',
        'Topics',
        'Travel',
        'U.S.',
        'Universal',
        'UrbanEye',
        'Washington',
        'Week in Review',
        'World',
        'Your Money',
    ];

    public const GUARDIAN_CATEGORIES = [
        'world',
        'international',
        'environment',
        'science',
        'football',
        'technology',
        'business',
        'obituaries',
        'books',
        'music',
        'film',
        'games',
        'stage',
        'fashion',
        'food',
        'lifestyle',
        'travel',
        'money',
    ];

    public static function getAllCategories(){
        $allCategories = array_merge(self::NEWS_API_CATEGORIES, self::NYT_CATEGORIES, self::GUARDIAN_CATEGORIES);
        $allCategories = array_map('strtolower', $allCategories);
        $allCategories = array_values(array_unique($allCategories));

        return array_reduce($allCategories, function($carry, $item){
            $carry[] = [
              'id' => $item,
              'name' => ucwords($item),
            ];
            return $carry;
        }, []);
    }

}
