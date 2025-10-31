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
            'label' => 'Home',
            'icon' => 'pi pi-search',
            'route' => '/home',
            'is_external' => 0,
            'position' => 1,
        ]);

        $resource = new MenuItemResource($menuItem);
        $resourceData = $resource->toArray(request());

        $this->assertEquals($menuItem->id, $resourceData['id']);
        $this->assertEquals('Home', $resourceData['label']);
        $this->assertEquals('pi pi-search', $resourceData['icon']);
        $this->assertEquals('/home', $resourceData['route']);
        $this->assertEquals(1, $resourceData['position']);
        $this->assertEquals(0, $resourceData['is_external']);
        $this->assertEquals($menu->id, $resourceData['menu_id']);

        // children debe existir siempre y ser array vacío cuando no está cargada
        $this->assertArrayHasKey('children', $resourceData);
        $this->assertIsArray($resourceData['children']);
        $this->assertCount(0, $resourceData['children']);
    }

    public function test_menu_item_resource_returns_correct_data_with_children()
    {
        $menu = Menu::factory()->create();
        $parentMenuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Parent',
            'icon' => 'pi pi-search',
            'route' => '/parent',
            'position' => 1,
        ]);

        $childMenuItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parentMenuItem->id,
            'label' => 'Child',
            'icon' => 'pi pi-heart',
            'route' => '/child',
            'position' => 1,
        ]);

        // Cargamos la relación children explícitamente
        $parentMenuItem->load('children');

        $resource = new MenuItemResource($parentMenuItem);
        $resourceData = $resource->toArray(request());

        $this->assertEquals($parentMenuItem->id, $resourceData['id']);
        $this->assertEquals('Parent', $resourceData['label']);
        $this->assertEquals('pi pi-search', $resourceData['icon']);
        $this->assertEquals('/parent', $resourceData['route']);
        $this->assertEquals(1, $resourceData['position']);
        $this->assertEquals($menu->id, $resourceData['menu_id']);

        $this->assertArrayHasKey('children', $resourceData);
        $this->assertIsArray($resourceData['children']);
        $this->assertCount(1, $resourceData['children']);
        $this->assertEquals($childMenuItem->id, $resourceData['children'][0]['id']);
        $this->assertEquals('pi pi-heart', $resourceData['children'][0]['icon']);
    }
}
