<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MenuItem::factory()->create([
            'label' => "Home",
            'icon' => "pi pi-home",
            'url' => "/",
            'is_external' => 0,
            'position' => 1,
            'menu_id' => 1,
        ]);

        MenuItem::factory()->create([
            'label' => "Blog",
            'icon' => "pi pi-briefcase",
            'url' => "/blog",
            'is_external' => 0,
            'position' => 2,
            'menu_id' => 1,
        ]);

        MenuItem::factory()->create([
            'label' => "Categorias",
            'icon' => "pi pi-briefcase",
            'url' => "/categorias",
            'is_external' => 0,
            'position' => 3,
            'menu_id' => 1,
        ]);
    }
}
