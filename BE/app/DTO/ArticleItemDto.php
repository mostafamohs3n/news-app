<?php

namespace App\DTO;

class ArticleItemDto
{
    /**
     * @param  string  $id
     * @param  string  $title
     * @param  string  $excerpt
     * @param  string  $contentUrl
     * @param  string|null  $thumbnail
     * @param  string  $date
     * @param  string|null  $externalSource
     * @param  array|null  $externalSourceData
     * @param  string|null  $source
     * @param  string|null  $sourceName
     * @param  string|null  $category
     * @param  string|null  $author
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $excerpt,
        public readonly string $contentUrl,
        public readonly ?string $thumbnail,
        public readonly string $date,
        public readonly ?string $externalSource,
        public readonly ?array $externalSourceData,
        public readonly ?string $source,
        public readonly ?string $sourceName,
        public ?string $category,
        public readonly ?string $author
    ) {
    }

    public function formatToArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'contentUrl' => $this->contentUrl,
            'thumbnail' => $this->thumbnail,
            'date' => $this->date,
            'externalSource' => $this->externalSource,
            'externalSourceData' => $this->externalSourceData,
            'source' => $this->source,
            'source_name' => $this->sourceName,
            'category' => $this->category,
            'author' => $this->author,
        ];
    }
}
