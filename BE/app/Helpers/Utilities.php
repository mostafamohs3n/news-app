<?php

namespace App\Helpers;

use App\Enums\CacheKeyEnum;

class Utilities
{
    /**
     * @param $str
     * @param $maxLength
     * @return string
     */
    public static function getExcerpt($str, $maxLength = 400)
    {
        $str = strip_tags($str);
        if (strlen($str) > $maxLength) {
            $excerpt = substr($str, 0, $maxLength - 3);
            $lastSpaceLocation = strrpos($excerpt, ' ');
            $excerpt = substr($excerpt, 0, $lastSpaceLocation);
            if ($excerpt[strlen($excerpt) - 1] != '.') {
                $excerpt .= '...';
            }
        } else {
            $excerpt = $str;
        }

        return $excerpt;
    }

    /**
     * @param $params
     * @return string
     */
    public static function getArticlesCacheKey($params): string
    {
        $cacheKey = CacheKeyEnum::ARTICLES;
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $cacheKey .= "_{$key}={$value}";
        }
        return $cacheKey;
    }

}
