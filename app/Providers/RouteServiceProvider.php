<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define the routes for the application.
     */
    public function boot(): void
    {
        // 1. Configura los Rate Limiters personalizados
        $this->configureRateLimiting();

        // 2. Define las rutas (api/web)
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Define the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // 🔹 Límite general para toda la API (100/min por usuario o IP)
        RateLimiter::for('api', function (Request $request) {
            $key = optional($request->user())->id ?: $request->ip();

            return Limit::perMinute(100)->by($key)
                ->response(function () {
                    return response()->json([
                        'message' => 'Demasiadas peticiones. Intenta de nuevo en un momento.',
                    ], 429)->header('Retry-After', 60);
                });
        });

        // 🔹 Límite estricto para login/register/resend (6/min por IP + email)
        // Usamos hash del email para no guardar el valor en claro en el key.
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email', '');
            $emailHash = $email !== '' ? hash('sha256', $email) : '';

            // Combina IP + hash(email) -> más robusto contra abuso por fuerza bruta.
            $key = $request->ip() . '|' . $emailHash;

            return Limit::perMinute(6)->by($key)
                ->response(function () {
                    return response()->json([
                        'message' => 'Demasiadas solicitudes de autenticación. Espera un minuto.',
                    ], 429)->header('Retry-After', 60);
                });
        });

        // 🔹 Límite para endpoints "pesados" (10/min por usuario o IP)
        RateLimiter::for('heavy', function (Request $request) {
            $key = optional($request->user())->id ?: $request->ip();

            return Limit::perMinute(10)->by($key)
                ->response(function () {
                    return response()->json([
                        'message' => 'Demasiadas peticiones a este recurso. Reduce la frecuencia.',
                    ], 429)->header('Retry-After', 60);
                });
        });
    }
}
