<?php

namespace Tests\Feature\Models;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_menu_can_be_created()
    {
        $menu = Menu::factory()->create();
        $this->assertNotNull($menu);
        $this->assertDatabaseHas('menus', ['name' => $menu->name]);
    }

    public function test_menu_has_fillable_attributes()
    {
        $menu = new Menu([
            'name' => 'Main Menu',
        ]);

        $this->assertEquals('Main Menu', $menu->name);
    }

    public function test_menu_has_many_menu_items()
    {
        $menu = Menu::factory()->create();
        MenuItem::factory()->count(3)->create(['menu_id' => $menu->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $menu->items);
        $this->assertCount(3, $menu->items);
    }
}
