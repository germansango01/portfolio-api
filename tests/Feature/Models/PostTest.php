<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_post_can_be_created()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($post);
        $this->assertDatabaseHas('posts', ['title' => $post->title]);
    }

    public function test_post_has_fillable_attributes()
    {
        $user = User::factory()->create();
        $post = new Post([
            'title'   => 'Test Post',
            'slug'    => 'test-post',
            'content' => 'This is some test content.',
            'user_id' => $user->id,
        ]);

        $this->assertEquals('Test Post', $post->title);
        $this->assertEquals('test-post', $post->slug);
        $this->assertEquals('This is some test content.', $post->content);
    }

    public function test_post_belongs_to_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }

    public function test_post_belongs_to_category()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();
        $post = Post::factory()->create(['category_id' => $category->id, 'user_id' => $user->id]);

        $this->assertInstanceOf(Category::class, $post->category);
        $this->assertEquals($category->id, $post->category->id);
    }

    public function test_post_belongs_to_many_tags()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $tags = Tag::factory()->count(3)->create();
        $post->tags()->attach($tags);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $post->tags);
        $this->assertCount(3, $post->tags);
    }

    public function test_with_relations_scope_loads_relationships()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $post->tags()->attach(Tag::factory()->create());

        $loadedPost = Post::query()->withRelations()->find($post->id);

        $this->assertTrue($loadedPost->relationLoaded('user'));
        $this->assertTrue($loadedPost->relationLoaded('category'));
        $this->assertTrue($loadedPost->relationLoaded('tags'));
    }

    public function test_search_term_scope_filters_by_title_or_content()
    {
        $user = User::factory()->create();
        Post::factory()->create(['title' => 'Post with keyword', 'content' => 'Some content', 'user_id' => $user->id]);
        Post::factory()->create(['title' => 'Another post', 'content' => 'Content with keyword', 'user_id' => $user->id]);
        Post::factory()->create(['title' => 'No match', 'content' => 'No match', 'user_id' => $user->id]);

        $results = Post::query()->searchTerm('keyword')->get();

        $this->assertCount(2, $results);
        $this->assertEquals(1, $results->where('title', 'Post with keyword')->count());
        $this->assertEquals(1, $results->where('content', 'Content with keyword')->count());
    }

    public function test_filter_by_category_scope_filters_posts()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $user = User::factory()->create();

        Post::factory()->count(2)->create(['category_id' => $category1->id, 'user_id' => $user->id]);
        Post::factory()->count(1)->create(['category_id' => $category2->id, 'user_id' => $user->id]);

        $results = Post::query()->filterByCategory($category1->id)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($post) => $post->category_id === $category1->id));
    }

    public function test_filter_by_author_scope_filters_posts()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Post::factory()->count(2)->create(['user_id' => $user1->id]);
        Post::factory()->count(1)->create(['user_id' => $user2->id]);

        $results = Post::query()->filterByAuthor($user1->id)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($post) => $post->user_id === $user1->id));
    }

    public function test_filter_by_tag_scope_filters_posts()
    {
        $user = User::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $post1 = Post::factory()->create(['user_id' => $user->id]);
        $post1->tags()->attach($tag1);

        $post2 = Post::factory()->create(['user_id' => $user->id]);
        $post2->tags()->attach($tag1);
        $post2->tags()->attach($tag2);

        $post3 = Post::factory()->create(['user_id' => $user->id]);
        $post3->tags()->attach($tag2);

        $results = Post::query()->filterByTag($tag1->id)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn ($post) => $post->tags->contains($tag1)));
    }
}
