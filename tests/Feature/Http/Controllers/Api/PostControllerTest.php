<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): User
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], 'api');

        return $user;
    }

    public function test_summary_returns_blog_data()
    {
        $user = $this->authenticate();
        Post::factory()->count(10)->for($user)->create();
        Category::factory()->count(2)->create();

        $response = $this->getJson(route('api.v1.posts.summary'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'latest_posts',
                    'most_viewed_posts',
                    'posts_by_category',
                ],
            ]);
    }

    public function test_search_returns_paginated_posts()
    {
        $user = $this->authenticate();
        Post::factory()->count(20)->for($user)->create();

        $response = $this->getJson(route('api.v1.posts.search', ['q' => 'post']));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'posts',
                    'meta',
                    'links',
                ],
            ]);
    }

    public function test_posts_returns_paginated_posts()
    {
        $user = $this->authenticate();
        Post::factory()->count(15)->for($user)->create();

        $response = $this->getJson(route('api.v1.posts.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'posts',
                    'meta',
                    'links',
                ],
            ]);
    }

    public function test_posts_by_category_returns_posts()
    {
        $user = $this->authenticate();
        $category = Category::factory()->create();
        Post::factory()->count(5)->for($user)->for($category)->create();

        $response = $this->getJson(route('api.v1.posts.byCategory', $category->slug));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'posts',
                    'meta',
                    'links',
                ],
            ]);
    }

    public function test_posts_by_tag_returns_posts()
    {
        $user = $this->authenticate();
        $tag = Tag::factory()->create();
        $post = Post::factory()->for($user)->create();
        $post->tags()->attach($tag);

        $response = $this->getJson(route('api.v1.posts.byTag', $tag->slug));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'posts',
                    'meta',
                    'links',
                ],
            ]);
    }

    public function test_posts_by_user_returns_posts()
    {
        $user = $this->authenticate();
        Post::factory()->count(3)->for($user)->create();

        $response = $this->getJson(route('api.v1.posts.byUser', $user->id));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'posts',
                    'meta',
                    'links',
                ],
            ]);
    }

    public function test_show_returns_a_single_post()
    {
        $user = $this->authenticate();
        $post = Post::factory()->for($user)->create();

        $response = $this->getJson(route('api.v1.posts.show', $post->slug));

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'post' => [
                        'id',
                        'title',
                        'slug',
                        'content',
                        'views',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        // Validamos que los valores coincidan
        $this->assertEquals($post->id, $response->json('data.post.id'));
        $this->assertEquals($post->title, $response->json('data.post.title'));
        $this->assertEquals($post->slug, $response->json('data.post.slug'));
        $this->assertEquals($post->content, $response->json('data.post.content'));
    }

    public function test_show_returns_not_found_for_invalid_post()
    {
        $this->authenticate();

        $response = $this->getJson(route('api.v1.posts.show', 'invalid-slug'));

        $response->assertNotFound();
    }

    public function test_posts_by_category_returns_not_found_for_invalid_category()
    {
        $this->authenticate();

        $response = $this->getJson(route('api.v1.posts.byCategory', 'invalid-slug'));

        $response->assertNotFound();
    }

    public function test_posts_by_tag_returns_not_found_for_invalid_tag()
    {
        $this->authenticate();

        $response = $this->getJson(route('api.v1.posts.byTag', 'invalid-slug'));

        $response->assertNotFound();
    }

    public function test_posts_by_user_returns_not_found_for_invalid_user()
    {
        $this->authenticate();

        $response = $this->getJson(route('api.v1.posts.byUser', 999));

        $response->assertNotFound();
    }
}
