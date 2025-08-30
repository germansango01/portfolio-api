<?php

namespace Tests\Feature\Http\Resources;

use App\Http\Resources\MenuItemResource;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_item_resource_returns_correct_data_without_children()
    {
        $menu = Menu::factory()->create();
        $menuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'name' => 'Home',
            'title' => 'Home Page',
            'url' => '/home',
            'position' => 1,
        ]);

        $resource = new MenuItemResource($menuItem);
        $resourceData = $resource->toArray(request());

        $this->assertEquals($menuItem->id, $resourceData['id']);
        $this->assertEquals('Home', $resourceData['name']);
        $this->assertEquals('Home Page', $resourceData['title']);
        $this->assertEquals('/home', $resourceData['url']);
        $this->assertEquals(1, $resourceData['position']);
        $this->assertEquals($menu->id, $resourceData['menu_id']);
    }

    public function test_menu_item_resource_returns_correct_data_with_children()
    {
        $menu = Menu::factory()->create();
        $parentMenuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'name' => 'Parent',
            'title' => 'Parent Page',
            'url' => '/parent',
            'position' => 1,
        ]);

        $childMenuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parentMenuItem->id,
            'name' => 'Child',
            'title' => 'Child Page',
            'url' => '/child',
            'position' => 1,
        ]);

        $parentMenuItem->load('children');

        $resource = new MenuItemResource($parentMenuItem);
        $resourceData = $resource->toArray(request());

        $this->assertEquals($parentMenuItem->id, $resourceData['id']);
        $this->assertEquals('Parent', $resourceData['name']);
        $this->assertEquals('Parent Page', $resourceData['title']);
        $this->assertEquals('/parent', $resourceData['url']);
        $this->assertEquals(1, $resourceData['position']);
        $this->assertEquals($menu->id, $resourceData['menu_id']);
        $this->assertArrayHasKey('children', $resourceData);
        $this->assertCount(1, $resourceData['children']);
        $this->assertEquals($childMenuItem->id, $resourceData['children'][0]['id']);
    }
}
