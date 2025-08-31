<?php

namespace Tests\Feature\Http\Resources;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_resource_returns_correct_data()
    {
        $tag = Tag::factory()->create();

        $resource = new TagResource($tag);
        $resourceData = $resource->toArray(request());

        $this->assertEquals($tag->id, $resourceData['id']);
        $this->assertEquals($tag->name, $resourceData['name']);
        $this->assertEquals($tag->slug, $resourceData['slug']);
    }
}
