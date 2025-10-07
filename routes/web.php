<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-email', function () {
    Mail::raw('Este es un correo de prueba desde Laragon con Mailpit hola karla.', function ($message) {
        $message->to('test@example.com')
                ->subject('Correo de prueba de karla');
    });

    return '¡Correo de prueba enviado!';
});
