<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ArticleCategoryController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ArticleSourceController;
use App\Http\Controllers\Api\User\UserPreferenceController as UserPreferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function(){
  Route::get('/user', [AuthController::class, 'user']);
  Route::post('auth/logout', [AuthController::class, 'logout']);
  Route::resource('user-preference', UserPreferenceController::class)->only('index', 'store');
});

Route::middleware('guest')->group(function(){
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
});


Route::get('articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('sources', [ArticleSourceController::class, 'index'])->name('sources.index');
Route::get('categories', [ArticleCategoryController::class, 'index'])->name('categories.index');
