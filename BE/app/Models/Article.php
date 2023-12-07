<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id', 'id');
    }

    public function source()
    {
        return $this->belongsTo(ArticleSource::class, 'article_source_id', 'id');
    }

    public function externalSource()
    {
        return $this->belongsTo(ArticleExternalSource::class, 'article_external_source_id', 'id');
    }

    public function scopeSearch($query, $queryString)
    {
        if (empty($queryString)) {
            return $query;
        }
        return $query->where(function ($q) use ($queryString) {
            return $q->where('title', 'LIKE', '%'.$queryString.'%')
                     ->orWhere('excerpt', 'LIKE', '%'.$queryString.'%')
            ;
        });
    }

    public function scopeFromDate($query, $fromDate)
    {
        if (empty($fromDate)) {
            return $query;
        }
        return $query->where('publish_date', '>=', $fromDate);
    }

    public function scopeToDate($query, $toDate)
    {
        if (empty($toDate)) {
            return $query;
        }
        return $query->where('publish_date', '<=', $toDate);
    }

    public function scopeSources($query, $sources)
    {
        if (empty($sources)) {
            return $query;
        }
        return $query->whereIn('article_source_id', $sources);
    }

    public function scopeExternalSources($query, $externalSources)
    {
        if (empty($externalSources)) {
            return $query;
        }
        return $query->whereIn('article_external_source_id', $externalSources);
    }

    public function scopeCategories($query, $categories, $sources)
    {
        if (empty($categories)) {
            return $query;
        }
        $categoriesList = ArticleCategory::whereIn('identifier', $categories);
        if (!empty($sources)) {
            $categoriesList = $categoriesList->whereIn('article_source_id', $sources);
        }

        $categoriesIds = $categoriesList->get()
                                        ->pluck('id')
        ;
        return $query->whereIn('article_category_id', $categoriesIds);
    }

    public function formatToArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'contentUrl' => $this->content_url,
            'thumbnail' => $this->thumbnail,
            'date' => $this->publish_date,
            'externalSource' => $this->externalSource?->name,
            'source' => $this->source?->name,
            'category' => $this->category?->name,
            'author' => $this->author,
        ];
    }
}
