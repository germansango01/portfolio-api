<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\MenuRequest;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MenuRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_id_is_required()
    {
        $request = new MenuRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('menu_id', $validator->errors()->toArray());
    }

    public function test_menu_id_must_exist_in_menus_table()
    {
        $request = new MenuRequest();
        $rules = $request->rules();

        $validator = Validator::make(['menu_id' => 999], $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('menu_id', $validator->errors()->toArray());
    }

    public function test_valid_menu_id_passes_validation()
    {
        $menu = Menu::factory()->create();

        $request = new MenuRequest();
        $rules = $request->rules();

        $validator = Validator::make(['menu_id' => $menu->id], $rules);

        $this->assertTrue($validator->passes());
    }
}
