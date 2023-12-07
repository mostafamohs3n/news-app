<?php

namespace App\Interfaces;

use Illuminate\Support\Facades\Request;

interface ArticleFetcher
{
    public function getAll(): mixed;
}
