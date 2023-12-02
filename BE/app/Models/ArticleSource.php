<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleSource extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function externalSources(){
        return $this->hasMany(ArticleExternalSource::class);
    }

    public function categories(){
        return $this->hasMany(ArticleCategory::class);
    }

    public function articles(){
        return $this->hasMany(Article::class);
    }
}
