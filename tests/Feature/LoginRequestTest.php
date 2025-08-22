<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    public function test_returns_validation_errors_when_email_and_password_are_missing()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors',
        ]);

        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Validation Error',
        ]);

        $this->assertContains(__('validation.email_required'), $response->json('errors'));
        $this->assertContains(__('validation.password_required'), $response->json('errors'));
    }

    public function test_returns_validation_error_for_invalid_email_format()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'secret123',
        ]);

        $response->assertStatus(422);
        $this->assertContains(__('validation.email_invalid'), $response->json('errors'));
    }
}
