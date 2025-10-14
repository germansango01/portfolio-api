<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->name('api.v1.')->group(function () {

    /* -------------------- RUTAS PÚBLICAS -------------------- */

    Route::group(['middleware' => ['throttle:login']], function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('/register', 'register')->name('register');
            Route::post('/login', 'login')->name('login');
            Route::post('/email/resend', 'resend')->name('verification.send');
            Route::post('/email/resend-guest', 'resendVerificationLink')->name('verification.resend.guest');
            Route::post('/password/forgot', 'forgotPassword')->name('password.forgot');
            Route::post('/password/reset', 'resetPassword')->name('password.reset');
        });
    });

    // Verificación de email
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    /* -------------------- RUTAS PROTEGIDAS -------------------- */
    Route::group(['middleware' => ['auth:api', 'throttle:api']], function () {

        Route::controller(AuthController::class)->group(function () {
            Route::post('/logout', 'logout')->name('logout');
            Route::get('/user', 'user')->name('user');
        });

        Route::controller(CategoryController::class)->group(function () {
            Route::get('/categories', 'index')->name('categories.index');
            Route::get('/category/{slug}', 'show')->name('categories.show');
        });

        Route::get('/menus/{menu:id}', [MenuController::class, 'index'])->name('menus.index');

        Route::controller(PostController::class)->group(function () {
            Route::get('/posts/summary', 'summary')->name('posts.summary')->middleware('throttle:heavy');
            Route::get('/posts/search', 'search')->name('posts.search')->middleware('throttle:heavy');
            Route::get('/posts/category/{category:slug}', 'postsByCategory')->name('posts.byCategory');
            Route::get('/posts/tag/{tag:slug}', 'postsByTag')->name('posts.byTag');
            Route::get('/posts/user/{user}', 'postsByUser')->name('posts.byUser');
            Route::get('/posts/{slug}', 'show')->name('posts.show');
            Route::get('/posts', 'index')->name('posts.index');
        });

        Route::controller(TagController::class)->group(function () {
            Route::get('/tags', 'index')->name('tags.index');
            Route::get('/tag/{slug}', 'show')->name('tags.show');
        });

    });
});
