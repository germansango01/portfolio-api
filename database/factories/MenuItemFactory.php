<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $primeIcons = [
            'pi pi-user',
            'pi pi-cog',
            'pi pi-calendar',
            'pi pi-envelope',
            'pi pi-heart',
            'pi pi-star',
            'pi pi-search',
            'pi pi-briefcase',
            'pi pi-home',
            'pi pi-times',
            'pi pi-book',
            'pi pi-phone',
        ];

        $rand = rand(0, 1);

        return [
            'label' => $this->faker->unique()->word,
            'icon' => Arr::random($primeIcons),
            'url' => $rand ? $this->faker->url : $this->faker->regexify("/category/[a-z]{6,14}") ,
            'is_external' => $rand,
            'position' => $this->faker->numberBetween(1, 10),
            'parent_id' => null,
        ];
    }
}
