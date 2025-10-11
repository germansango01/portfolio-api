<?php

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_tag_can_be_created()
    {
        $tag = Tag::factory()->create();
        $this->assertNotNull($tag);
        $this->assertDatabaseHas('tags', ['name' => $tag->name]);
    }

    public function test_tag_has_fillable_attributes()
    {
        $tag = new Tag([
            'name' => 'Test Tag',
            'slug' => 'test-tag',
        ]);

        $this->assertEquals('Test Tag', $tag->name);
        $this->assertEquals('test-tag', $tag->slug);
    }

    public function test_tag_belongs_to_many_posts()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);
        $tag->posts()->attach($posts);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $tag->posts);
        $this->assertCount(3, $tag->posts);
    }
}
