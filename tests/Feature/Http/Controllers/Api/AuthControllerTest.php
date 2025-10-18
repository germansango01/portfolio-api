<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
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

    public function test_register_creates_new_user_and_sends_verification_email()
    {
        Notification::fake();

        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson(route('api.v1.register'), $data);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth.success_register_pending_verification'),
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john.doe@gmail.com']);
    }

    public function test_register_returns_error_with_invalid_data()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson(route('api.v1.register'), $data);

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
            'email' => 'john.doe@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $data = [
            'email' => 'john.doe@gmail.com',
            'password' => 'password',
        ];

        $response = $this->postJson(route('api.v1.login'), $data);

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
            'email' => 'john.doe@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $data = [
            'email' => 'john.doe@gmail.com',
            'password' => 'wrong-password',
        ];

        $response = $this->postJson(route('api.v1.login'), $data);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_logout_revokes_user_token()
    {
        $user = $this->authenticate();

        $response = $this->postJson(route('api.v1.logout'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth.success_logout'),
            ]);
    }

    public function test_user_returns_authenticated_user()
    {
        $user = $this->authenticate();

        $response = $this->getJson(route('api.v1.user'));

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

    public function test_verify_email_verifies_user_and_redirects()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $verificationUrl = $this->getVerificationUrl($user);

        $response = $this->get($verificationUrl);

        $this->assertNotNull($user->fresh()->email_verified_at);

        $response->assertRedirect(env('FRONTEND_AUTH_URL') . '/verify?status=success');
    }

    public function test_verify_email_with_already_verified_user_redirects()
    {
        $user = User::factory()->create();

        $verificationUrl = $this->getVerificationUrl($user);

        $response = $this->get($verificationUrl);

        $response->assertRedirect(env('FRONTEND_AUTH_URL') . '/verify?status=already-verified');
    }

    public function test_verify_email_with_invalid_signature_returns_403()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $invalidUrl = route('api.v1.verification.verify', ['id' => $user->id, 'hash' => 'invalid-hash']);

        $response = $this->get($invalidUrl);

        $response->assertStatus(403);
    }

    private function getVerificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'api.v1.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }
}
