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

        // Creamos un menú
        $menu = Menu::factory()->create();

        // Creamos 3 items con posición fija
        $menuItems = collect([
            MenuItem::factory()->create([
                'menu_id' => $menu->id,
                'position' => 1,
                'is_active' => 1,
            ]),
            MenuItem::factory()->create([
                'menu_id' => $menu->id,
                'position' => 2,
                'is_active' => 1,
            ]),
            MenuItem::factory()->create([
                'menu_id' => $menu->id,
                'position' => 3,
                'is_active' => 1,
            ]),
        ]);

        $response = $this->getJson(route('api.v1.menus.index', $menu->id));

        // Validamos la estructura del JSON
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
            ->assertJsonCount($menuItems->count(), 'data.menus');

        // Validamos que cada item coincida con la BD, usando el orden por posición
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
