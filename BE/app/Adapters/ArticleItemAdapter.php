<?php

namespace App\Adapters;

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

    public function adapt()
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
            'category' => $this->getCategory(),
            'author' => $this->getAuthor(),
        ];
    }

    private function generateId()
    {
        return md5(trim(strtolower($this->getTitle())));
    }

    private function getTitle()
    {
        return $this->article['title'] ?? $this->article['webTitle'] ?? $this->article['headline']['main'] ?? 'N/A';
    }

    private function getExcerpt()
    {
        $excerpt = strip_tags($this->article['description'] ?? $this->article['fields']['body'] ?? $this->article['snippet'] ?? '');
        return !empty($excerpt) ? Utilities::getExcerpt($excerpt) : 'Content Unavailable';
    }

    private function getContentUrl()
    {
        return $this->article['url'] ?? $this->article['webUrl'] ?? $this->article['web_url'] ?? null;
    }

    private function getThumbnail()
    {
        return $this->article['urlToImage'] ?? $this->article['fields']['thumbnail'] ?? $this->getDefaultThumbnail();
    }

    private function getDefaultThumbnail()
    {
        return $this->getExternalSource() == ArticleSourceEnum::NYT_API_NAME
            ? 'https://1000logos.net/wp-content/uploads/2017/04/New-York-Times-logo.png'
            : null;
    }

    private function getExternalSource()
    {
        return $this->article['source']['name'] ?? (
        isset($this->article['pillarName'])
            ? ArticleSourceEnum::GUARDIAN_API_NAME
            : (isset($this->article['news_desk'])
            ? ArticleSourceEnum::NYT_API_NAME
            : null)
        );
    }

    private function getExternalSourceData(){
        $externalSourceData = [];
        if(!empty($this->article['source']) && is_array($this->article['source'])){
            $externalSourceData = [
                'id' => $this->article['source']['id'] ?? Str::slug($this->getExternalSource()),
                'name' => $this->article['source']['name'],
            ];
        }
        return $externalSourceData;
    }

    private function getDate()
    {
        $date = $this->article['publishedAt'] ?? $this->article['webPublicationDate'] ?? $this->article['pub_date'] ?? null;

        return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    private function getSource()
    {
        $apiSrc = $this->getExternalSource();
        return in_array($apiSrc, [ArticleSourceEnum::GUARDIAN_API_ID, ArticleSourceEnum::NYT_API_ID])
            ? $apiSrc
            : ArticleSourceEnum::NEWS_API_ID;
    }

    private function getCategory()
    {
        return $this->article['pillarName'] ?? $this->article['section_name'] ?? null;
    }

    private function getAuthor()
    {
        //@TODO: This needs more accurate normalization
        $author = $this->article['author'] ?? $this->article['byline']['original'] ?? $this->article['byline'] ?? null;
        return trim(current(explode(',', $author)));
    }
}
