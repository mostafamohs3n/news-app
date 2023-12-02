<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique();
            $table->string('title');
            $table->longText('excerpt');
            $table->string('content_url');
            $table->string('thumbnail');
            $table->timestamp('publish_date');
            $table->foreignId('article_source_id')->nullable()->constrained();
            $table->foreignId('article_category_id')->nullable()->constrained();
            $table->foreignId('article_external_source_id')->nullable()->constrained();
            $table->string('author')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
