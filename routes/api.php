<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

/* Rutas públicas */

/* Rutas de autenticación */
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register')->name('api.register');
    Route::post('/login', 'login')->name('api.login');
    Route::get('email/verify/{id}/{hash}', 'verify')->name('verification.verify');
    Route::post('email/resend', 'resend')->name('verification.send');
});

/* Rutas protegidas */
Route::group(['middleware' => 'auth:api'], function () {
    /* Rutas de autenticación */
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout')->name('api.logout');
        Route::get('/user', 'user')->name('api.user');
    });

    /* Rutas de categorías */
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'index')->name('api.categories');
        Route::get('/category/{slug}', 'show')->name('api.category');
    });

    /* Rutas de menú */
    Route::get('/menus/{menu:id}', [MenuController::class, 'index'])->name('api.menu.index');

    /* Rutas de posts */
    Route::controller(PostController::class)->group(function () {
        Route::get('/posts/summary', 'summary')->name('api.posts.summary');
        Route::get('/posts/search', 'search')->name('api.posts.search');
        Route::get('/posts/category/{category:slug}', 'postsByCategory')->name('api.posts.byCategory');
        Route::get('/posts/tag/{tag:slug}', 'postsByTag')->name('api.posts.byTag');
        Route::get('/posts/user/{user}', 'postsByUser')->name('api.posts.byUser');
        Route::get('/posts/{slug}', 'show')->name('api.posts.show');
        Route::get('/posts', 'index')->name('api.posts.index');
    });

    /* Rutas de etiquetas */
    Route::controller(TagController::class)->group(function () {
        Route::get('/tags', 'index')->name('api.tags');
        Route::get('/tag/{slug}', 'show')->name('api.tag');
    });
});
