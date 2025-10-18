<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): User
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], 'api');

        return $user;
    }

    public function test_index_returns_menu_items_for_valid_menu_id()
    {
        $this->authenticate();
        $menu = Menu::factory()->create();
        MenuItem::factory()->count(3)->create(['menu_id' => $menu->id]);

        $response = $this->getJson(route('api.v1.menu.index', $menu->id));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items',
                ],
            ])
            ->assertJsonCount(3, 'data.items');
    }

    public function test_index_returns_error_when_menu_id_does_not_exist()
    {
        $this->authenticate();

        $response = $this->getJson(route('api.v1.menu.index', 999));

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }
}
