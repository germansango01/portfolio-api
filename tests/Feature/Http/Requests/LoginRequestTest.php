<?php

namespace Tests\Feature\Http\Requests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_validation_errors_when_email_and_password_are_missing_using_helpers()
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422);

        // Verifica que existan errores en esos campos (sin comprobar texto)
        $response->assertJsonValidationErrors(['email', 'password']);

        // Si además quieres comprobar los textos exactos:
        $this->assertEquals(__('validation.email_required'), $response->json('errors.email.0'));
        $this->assertEquals(__('validation.password_required'), $response->json('errors.password.0'));
    }

    public function test_returns_validation_error_for_invalid_email_format()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'not-an-email',
            'password' => 'secret123',
        ]);

        $response->assertStatus(422);

        // Comprueba que existe un error de validación para el campo 'email'
        $response->assertJsonValidationErrors(['email']);

        // Comprueba que el mensaje devuelto sea el esperado
        $this->assertEquals(
            __('validation.email_invalid'),
            $response->json('errors.email.0'),
        );
    }
}
