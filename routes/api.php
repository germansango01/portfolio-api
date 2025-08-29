<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

/* Rutas públicas */
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

/* Rutas protegidas */
Route::group(['middleware' => 'auth:api'], function () {
    /* Rutas de autenticación */
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout')->name('api.logout');
        Route::get('/user', 'user')->name('api.user');
    });
    /* Rutas de posts */
    Route::controller(PostController::class)->group(function () {
        Route::get('/resume', 'resume')->name('api.posts.resume');
        Route::get('/search', 'search')->name('api.posts.search');
        Route::get('/posts', 'posts')->name('api.posts.index');
        Route::get('/posts/category/{category:slug}', 'postsByCategory')->name('api.posts.byCategory');
        Route::get('/posts/tag/{tag:slug}', 'postsByTag')->name('api.posts.byTag');
        Route::get('/posts/user/{user}', 'postsByUser')->name('api.posts.byUser');
    });
});
