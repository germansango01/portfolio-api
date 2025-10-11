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
|
| Todo el API v1 está bajo el prefijo 'v1' y usa el nombre 'api.v1.'
|
*/

// Todo el API v1 está encapsulado aquí
Route::prefix('v1')->name('api.v1.')->group(function () {

    /* -------------------- RUTAS PÚBLICAS -------------------- */

    // Rutas de autenticación (públicas para registro/login/resend)
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register')->name('register')->middleware('throttle:login');
        Route::post('/login', 'login')->name('login')->middleware('throttle:login');
        Route::post('/email/resend', 'resend')->name('verification.send')->middleware('throttle:login');
    });

    // Verificación de email: firmada y limitada por throttle
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // -------------------------------------------------------------------

    /* -------------------- RUTAS PROTEGIDAS (AUTH REQUERIDA) -------------------- */
    /*
      Estas rutas requieren autenticación ('auth:api')
      y aplican límite de velocidad por usuario autenticado ('throttle:api').
    */
    Route::group(['middleware' => ['auth:api', 'throttle:api']], function () {

        /* Rutas de autenticación (autenticadas: logout, obtener usuario) */
        Route::controller(AuthController::class)->group(function () {
            Route::post('/logout', 'logout')->name('logout');
            Route::get('/user', 'user')->name('user');
        });

        /* -------------------- Rutas de Lectura/CRUD Protegidas -------------------- */

        /* Categorías  */
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/categories', 'index')->name('categories.index');
            Route::get('/category/{slug}', 'show')->name('categories.show');
        });

        /* Menús */
        Route::get('/menus/{menu:id}', [MenuController::class, 'index'])->name('menus.index');

        /* Posts */
        Route::controller(PostController::class)->group(function () {
            // Endpoints pesados con throttle:heavy
            Route::get('/posts/summary', 'summary')->name('posts.summary')->middleware('throttle:heavy');
            Route::get('/posts/search', 'search')->name('posts.search')->middleware('throttle:heavy');

            // Endpoints de lectura estándar
            Route::get('/posts/category/{category:slug}', 'postsByCategory')->name('posts.byCategory');
            Route::get('/posts/tag/{tag:slug}', 'postsByTag')->name('posts.byTag');
            Route::get('/posts/user/{user}', 'postsByUser')->name('posts.byUser');
            Route::get('/posts/{slug}', 'show')->name('posts.show');
            Route::get('/posts', 'index')->name('posts.index');
        });

        /* Etiquetas */
        Route::controller(TagController::class)->group(function () {
            Route::get('/tags', 'index')->name('tags.index');
            Route::get('/tag/{slug}', 'show')->name('tags.show');
        });

    });
});
