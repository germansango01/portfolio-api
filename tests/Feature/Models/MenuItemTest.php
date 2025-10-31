<?php

namespace Tests\Feature\Models;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
        // ahora comprobamos por 'label' (tu modelo usa 'label' en vez de 'name')
        $this->assertDatabaseHas('menu_items', ['label' => $menuItem->label]);
    }

    public function test_menu_item_has_fillable_attributes()
    {
        // para probar fillable sin persistir puedes usar new, pero para asegurar mass assignment, usa create()
        $menuId = Menu::factory()->create()->id;

        $menuItem = MenuItem::create([
            'label' => 'Home',
            'icon' => 'pi pi-home',
            'url' => '/home',
            'parent_id' => null,
            'position' => 1,
            'menu_id' => $menuId,
        ]);

        $this->assertEquals('Home', $menuItem->label);
        $this->assertEquals('pi pi-home', $menuItem->icon);
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

        $this->assertInstanceOf(EloquentCollection::class, $parent->children);
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
