<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

/* Rutas públicas */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/* Rutas protegidas */
Route::group(['middleware' => 'auth:api'], function () {
    /* Rutas de autenticación */
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/user', 'user');
    });
    /* Rutas de páginas */
    Route::controller(PostController::class)->group(function () {
        Route::get('/blog', 'blog');
        Route::get('/posts', 'posts');
        Route::get('/search', 'search');
    });
});
