<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
// Asegúrate de que los siguientes namespaces NO son necesarios aquí si ya están en RouteServiceProvider.php,
// pero en este enfoque moderno, las funciones de RateLimiter deben usarse en el array 'limits'.
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\RateLimiter; // ¡No es necesario aquí, ya que se usa la sintaxis del array!

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',

        // ************************************************
        // PASO CLAVE: DEFINICIÓN DE RATE LIMITERS
        // ************************************************
        limits: [
            // Límite general (100/min por usuario o IP)
            'api' => Limit::perMinute(100)->by(optional(request()->user())->id ?: request()->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Demasiadas peticiones. Intenta de nuevo en un momento.',
                    ], 429);
                }),

            // Límite estricto para autenticación (6/min por IP + email)
            'login' => Limit::perMinute(6)->by(request()->ip() . '|' . request()->input('email'))
                ->response(function () {
                    return response()->json([
                        'message' => 'Demasiadas solicitudes de autenticación. Espera un minuto.',
                    ], 429);
                }),

            // Límite para endpoints pesados (10/min por usuario o IP)
            'heavy' => Limit::perMinute(10)->by(optional(request()->user())->id ?: request()->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Demasiadas peticiones a este recurso. Reduce la frecuencia.',
                    ], 429);
                }),
        ],
        // ************************************************
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ...
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ...
    })->create();
