<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): User
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], 'api');

        return $user;
    }

    public function test_register_creates_new_user_and_returns_token()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson(route('api.register'), $data);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john.doe@example.com']);
    }

    public function test_register_returns_error_with_invalid_data()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson(route('api.register'), $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                    'success',
                    'message',
                    'errors' => [],
                ]);
    }

    public function test_login_returns_token_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password'),
        ]);

        $data = [
            'email' => 'john.doe@example.com',
            'password' => 'password',
        ];

        $response = $this->postJson(route('api.login'), $data);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token'],
            ]);
    }

    public function test_login_returns_error_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password'),
        ]);

        $data = [
            'email' => 'john.doe@example.com',
            'password' => 'wrong-password',
        ];

        $response = $this->postJson(route('api.login'), $data);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_logout_revokes_user_token()
    {
        $user = $this->authenticate();

        $response = $this->postJson(route('api.logout'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth.success_logout'),
            ]);
    }

    public function test_user_returns_authenticated_user()
    {
        $user = $this->authenticate();

        $response = $this->getJson(route('api.user'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user'],
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
            ]);
    }
}
