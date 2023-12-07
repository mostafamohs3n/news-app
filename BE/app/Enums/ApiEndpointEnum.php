<?php

namespace App\Enums;

enum ApiEndpointEnum
{
    public const NEWS_API_SOURCES = 'sources';

    public const NEWS_API_LISTING = 'everything';

    public const NEWS_API_TOP_HEADLINES = 'top-headlines';

    public const GUARDIAN_LISTING = 'search';

    public const NYT_LISTING = 'articlesearch.json';

}
