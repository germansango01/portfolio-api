<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_category_can_be_created()
    {
        $category = Category::factory()->create();
        $this->assertNotNull($category);
        $this->assertDatabaseHas('categories', ['name' => $category->name]);
    }

    public function test_category_has_fillable_attributes()
    {
        $category = new Category([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('test-category', $category->slug);
    }

    public function test_category_has_many_posts()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();
        Post::factory()->count(3)->create(['category_id' => $category->id, 'user_id' => $user->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $category->posts);
        $this->assertCount(3, $category->posts);
    }
}
