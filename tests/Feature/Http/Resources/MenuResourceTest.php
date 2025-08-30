<?php

namespace Tests\Feature\Http\Resources;

use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_resource_returns_correct_data()
    {
        $menu = Menu::factory()->create([
            'name' => 'Main Menu',
        ]);

        $resource = new MenuResource($menu);
        $resourceData = $resource->toArray(request());

        $this->assertEquals($menu->id, $resourceData['id']);
        $this->assertEquals('Main Menu', $resourceData['name']);
    }
}
