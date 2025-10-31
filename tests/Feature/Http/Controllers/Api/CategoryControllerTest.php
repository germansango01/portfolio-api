<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): User
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], 'api');
        return $user;
    }

    public function test_index_returns_a_list_of_categories(): void
    {
        $this->authenticate();

        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'categories',
                ],
            ])
            ->assertJsonCount(3, 'data.categories');
    }

    public function test_show_returns_a_single_category(): void
    {
        $this->authenticate();

        $category = Category::factory()->create();

        $response = $this->getJson('/api/v1/category/' . $category->slug);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'category',
                ],
            ])
            ->assertJson([
                'data' => [
                    'category' => [
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ],
                ],
            ]);
    }

    public function test_show_returns_a_404_error_if_the_category_does_not_exist(): void
    {
        $this->authenticate();

        $response = $this->getJson('/api/v1/categories/non-existent-slug');

        $response->assertNotFound();
    }
}
