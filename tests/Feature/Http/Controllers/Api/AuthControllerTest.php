<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
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

    public function test_verify_email_with_invalid_route_returns_403()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $invalidUrl = '/api/v1/email/verify/' . $user->id . '/invalid-hash';

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
            ],
        );
    }

    public function test_resend_sends_verification_email_to_unverified_user()
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);
        Passport::actingAs($user);

        $response = $this->postJson(route('api.v1.verification.send'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth.verification_link_resent'),
            ]);

        Notification::assertSentTo($user, CustomVerifyEmail::class);
    }

    public function test_resend_returns_error_for_verified_user()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson(route('api.v1.verification.send'));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => __('auth.email_already_verified'),
            ]);
    }

    public function test_resend_verification_link_sends_email_to_unverified_user()
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->postJson(route('api.v1.verification.resend.guest'), ['email' => $user->email]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth.verification_link_resent'),
            ]);

        Notification::assertSentTo($user, CustomVerifyEmail::class);
    }

    public function test_resend_verification_link_returns_error_for_verified_user()
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('api.v1.verification.resend.guest'), ['email' => $user->email]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => __('auth.email_already_verified'),
            ]);
    }

    public function test_resend_verification_link_returns_success_for_non_existent_user()
    {
        $response = $this->postJson(route('api.v1.verification.resend.guest'), ['email' => 'nonexistent@example.com']);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth.verification_link_sent_if_unverified'),
            ]);
    }

    public function test_forgot_password_sends_reset_link()
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->postJson(route('api.v1.password.forgot'), ['email' => $user->email]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('auth.password_reset_link_sent'),
            ]);

        Notification::assertSentTo($user, \App\Notifications\ResetPasswordNotification::class);
    }

    public function test_forgot_password_returns_error_for_non_existent_user()
    {
        $response = $this->postJson(route('api.v1.password.forgot'), ['email' => 'nonexistent@example.com']);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => __('auth.password_reset_link_failed'),
            ]);
    }

    public function test_reset_password_resets_password_and_returns_token()
    {
        $user = User::factory()->create();
        $token = 'test-token';

        \Illuminate\Support\Facades\DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        $response = $this->postJson(route('api.v1.password.reset'), [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token'],
            ]);

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_reset_password_returns_error_with_invalid_token()
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('api.v1.password.reset'), [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => __('auth.password_reset_failed'),
            ]);
    }
}
