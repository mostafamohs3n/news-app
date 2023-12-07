<?php

namespace App\Adapters;

use App\DTO\ArticleItemDto;
use App\Enums\ArticleSourceEnum;
use App\Helpers\Utilities;
use Illuminate\Support\Str;

class ArticleItemAdapter
{
    private array $article;

    public function __construct($article)
    {
        $this->article = $article;
    }

    public function formatToArray(): array
    {
        return [
            'id' => $this->generateId(),
            'title' => $this->getTitle(),
            'excerpt' => $this->getExcerpt(),
            'contentUrl' => $this->getContentUrl(),
            'thumbnail' => $this->getThumbnail(),
            'date' => $this->getDate(),
            'externalSource' => $this->getExternalSource(),
            'externalSourceData' => $this->getExternalSourceData(),
            'source' => $this->getSource(),
            'source_name' => $this->getSource(),
            'category' => $this->getCategory(),
            'author' => $this->getAuthor(),
        ];
    }

    public function generateId()
    {
        return md5(trim(strtolower($this->getTitle() . $this->getSource() . $this->getDate())));
    }

    public function getTitle()
    {
        return $this->article['title'] ?? $this->article['webTitle'] ?? $this->article['headline']['main'] ?? 'N/A';
    }

    public function getExcerpt()
    {
        $excerpt = strip_tags($this->article['description'] ?? $this->article['fields']['body'] ?? $this->article['snippet'] ?? '');
        return !empty($excerpt) ? Utilities::getExcerpt($excerpt) : 'Content Unavailable';
    }

    public function getContentUrl()
    {
        return $this->article['url'] ?? $this->article['webUrl'] ?? $this->article['web_url'] ?? null;
    }

    public function getThumbnail()
    {
        return $this->article['urlToImage'] ?? $this->article['fields']['thumbnail'] ?? $this->getDefaultThumbnail();
    }

    private function getDefaultThumbnail()
    {
        return $this->getExternalSource() == ArticleSourceEnum::NYT_API_NAME
            ? 'https://1000logos.net/wp-content/uploads/2017/04/New-York-Times-logo.png'
            : null;
    }

    public function getExternalSource()
    {
        return $this->article['source']['name'] ?? (
        isset($this->article['pillarName'])
            ? ArticleSourceEnum::GUARDIAN_API_NAME
            : (isset($this->article['news_desk'])
            ? ArticleSourceEnum::NYT_API_NAME
            : null)
        );
    }

    public function getDate()
    {
        $date = $this->article['publishedAt'] ?? $this->article['webPublicationDate'] ?? $this->article['pub_date'] ?? null;

        return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    public function getExternalSourceData()
    {
        $externalSourceData = [];
        if (!empty($this->article['source']) && is_array($this->article['source'])) {
            $externalSourceData = [
                'id' => $this->article['source']['id'] ?? Str::slug($this->getExternalSource()),
                'name' => $this->article['source']['name'],
            ];
        }
        return $externalSourceData;
    }

    public function getSource()
    {
        $apiSrc = $this->getExternalSource();
        return in_array($apiSrc, [ArticleSourceEnum::GUARDIAN_API_ID, ArticleSourceEnum::NYT_API_ID])
            ? $apiSrc
            : ArticleSourceEnum::NEWS_API_ID;
    }

    public function getCategory()
    {
        return $this->article['pillarName'] ?? $this->article['section_name'] ?? $this->article['category'];
    }

    public function getAuthor()
    {
        //@TODO: This needs more accurate normalization
        $author = $this->article['author'] ?? $this->article['byline']['original'] ?? $this->article['byline'] ?? null;
        $author = is_array($author) ? $author[0] ?? $author['person'] ?? '' : $author;
        if (empty($author)) {
            return '';
        }
        $author = trim(current(explode(',', $author)));
        return str_replace('By ', '', $author);
    }

    public function toDto(): ArticleItemDto
    {
        return new ArticleItemDto(
            id: $this->generateId(),
            title: $this->getTitle(),
            excerpt: $this->getExcerpt(),
            contentUrl: $this->getContentUrl(),
            thumbnail: $this->getThumbnail(),
            date: $this->getDate(),
            externalSource: $this->getExternalSource(),
            externalSourceData: $this->getExternalSourceData(),
            source: $this->getSource(),
            sourceName: $this->getSource(),
            category: $this->getCategory(),
            author: $this->getAuthor()
        );
    }
}
