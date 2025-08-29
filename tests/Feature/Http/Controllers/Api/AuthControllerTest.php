<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginWithValidCredentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(route('api.login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => ['token'],
                 ])
                 ->assertJson([
                     'success' => true,
                 ]);
    }

    public function testLoginWithInvalidCredentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(route('api.login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]));

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                 ]);
    }

    public function testLogout()
    {
        Passport::actingAs(User::factory()->create());

        $response = $this->postJson(route('api.logout'));

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);
    }

    public function testAuthenticatedUserRetrieval()
    {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this->getJson(route('api.user'));

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'user' => [
                             'id' => $user->id,
                             'email' => $user->email,
                         ],
                     ],
                 ]);
    }
}
