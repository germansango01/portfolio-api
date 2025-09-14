<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = Menu::all();

        MenuItem::factory()->count(15)->make()->each(function ($item) use ($menus) {
            $item->menu_id = $menus->random()->id;
            $item->save();

            // Add children to some items
            if (rand(0, 1)) {
                MenuItem::factory()->count(4)->create([
                    'menu_id' => $item->menu_id,
                    'parent_id' => $item->id,
                ]);
            }
        });
    }
}
