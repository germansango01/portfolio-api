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

        // Creamos menú y items activos
        $menu = Menu::factory()->create();
        $menuItems = MenuItem::factory()->count(3)->create([
            'menu_id' => $menu->id,
            'is_active' => 1, // asegurar que todos sean devueltos
        ]);

        $response = $this->getJson(route('api.v1.menus.index', $menu->id));

        // Validamos estructura general
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'parent' => [
                        'id',
                        'name',
                    ],
                    'menus' => [
                        '*' => [
                            'id',
                            'label',
                            'icon',
                            'route',
                            'position',
                            'is_external',
                            'menu_id',
                            'children',
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonCount($menuItems->count(), 'data.menus'); // usar count real

        // Validar que cada menuItem devuelto coincide con la BD
        foreach ($menuItems as $index => $item) {
            $this->assertEquals($item->id, $response->json("data.menus.$index.id"));
            $this->assertEquals($item->label, $response->json("data.menus.$index.label"));
            $this->assertEquals($item->icon, $response->json("data.menus.$index.icon"));
            $this->assertEquals($item->route, $response->json("data.menus.$index.route"));
            $this->assertEquals($item->position, $response->json("data.menus.$index.position"));
            $this->assertEquals($item->is_external, $response->json("data.menus.$index.is_external"));
            $this->assertEquals($item->menu_id, $response->json("data.menus.$index.menu_id"));
            $this->assertIsArray($response->json("data.menus.$index.children"));
        }
    }

    public function test_index_returns_error_when_menu_id_does_not_exist()
    {
        $this->authenticate();

        $response = $this->getJson(route('api.v1.menus.index', 999));

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
            ])
            ->assertJsonPath('success', false);
    }
}
