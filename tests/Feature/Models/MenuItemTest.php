<?php

namespace Tests\Feature\Models;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MenuItemTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_menu_item_can_be_created()
    {
        $menu = Menu::factory()->create();
        $menuItem = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $this->assertNotNull($menuItem);
        $this->assertDatabaseHas('menu_items', ['name' => $menuItem->name]);
    }

    public function test_menu_item_has_fillable_attributes()
    {
        $menuItem = new MenuItem([
            'name' => 'Home',
            'title' => 'Home Page',
            'url' => '/home',
            'parent_id' => null,
            'position' => 1,
            'menu_id' => Menu::factory()->create()->id,
        ]);

        $this->assertEquals('Home', $menuItem->name);
        $this->assertEquals('Home Page', $menuItem->title);
        $this->assertEquals('/home', $menuItem->url);
        $this->assertNull($menuItem->parent_id);
        $this->assertEquals(1, $menuItem->position);
    }

    public function test_menu_item_belongs_to_parent()
    {
        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $child = MenuItem::factory()->create(['parent_id' => $parent->id, 'menu_id' => $menu->id]);

        $this->assertInstanceOf(MenuItem::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_menu_item_has_many_children()
    {
        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->create(['menu_id' => $menu->id]);
        MenuItem::factory()->count(3)->create(['parent_id' => $parent->id, 'menu_id' => $menu->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $parent->children);
        $this->assertCount(3, $parent->children);
    }

    public function test_menu_item_belongs_to_menu()
    {
        $menu = Menu::factory()->create();
        $menuItem = MenuItem::factory()->create(['menu_id' => $menu->id]);

        $this->assertInstanceOf(Menu::class, $menuItem->menu);
        $this->assertEquals($menu->id, $menuItem->menu->id);
    }
}
