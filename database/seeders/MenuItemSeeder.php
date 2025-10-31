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
            'route' => "/",
            'url' => null,
            'is_external' => 0,
            'position' => 1,
            'menu_id' => 1,
        ]);

        MenuItem::factory()->create([
            'label' => "Blog",
            'icon' => "pi pi-briefcase",
            'route' => "/blog",
            'url' => null,
            'is_external' => 0,
            'position' => 2,
            'menu_id' => 1,
        ]);

        MenuItem::factory()->create([
            'label' => "Categorias",
            'icon' => "pi pi-briefcase",
            'route' => "/categorias",
            'url' => null,
            'is_external' => 0,
            'position' => 3,
            'menu_id' => 1,
        ]);
    }
}
