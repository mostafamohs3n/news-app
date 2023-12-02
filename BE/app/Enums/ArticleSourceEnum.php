<?php

namespace App\Enums;

class ArticleSourceEnum
{
    public const NEWS_API_ID = 'news-api';
    public const NEWS_API_NAME = 'News API';

    public const GUARDIAN_API_ID = 'guardian-api';
    public const GUARDIAN_API_NAME = 'The Guardian';

    public const NYT_API_ID = 'nyt-api';
    public const NYT_API_NAME = 'The New York Times';

    /**
     * @param $id
     * @return array
     */
    public static function getIdNamePair($id): array
    {
        return [
            'id' => $id,
            'name' => match ($id) {
                self::NEWS_API_ID => self::NEWS_API_NAME,
                self::GUARDIAN_API_ID => self::GUARDIAN_API_NAME,
                self::NYT_API_ID => self::NYT_API_NAME,
            },
        ];
    }


}
