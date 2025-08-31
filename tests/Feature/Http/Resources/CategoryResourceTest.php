<?php

namespace Tests\Feature\Http\Resources;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_resource_returns_correct_data()
    {
        $category = Category::factory()->create();

        $resource = new CategoryResource($category);
        $resourceData = $resource->toArray(request());

        $this->assertEquals($category->id, $resourceData['id']);
        $this->assertEquals($category->name, $resourceData['name']);
        $this->assertEquals($category->slug, $resourceData['slug']);
    }
}
