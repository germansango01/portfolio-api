<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_comment_can_be_created()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $this->assertNotNull($comment);
        $this->assertDatabaseHas('comments', ['body' => $comment->body]);
    }

    public function test_comment_has_fillable_attributes()
    {
        $comment = new Comment([
            'body' => 'This is a test comment.',
        ]);

        $this->assertEquals('This is a test comment.', $comment->body);
    }

    public function test_comment_belongs_to_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($user->id, $comment->user->id);
    }

    public function test_comment_belongs_to_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create(['post_id' => $post->id, 'user_id' => $user->id]);

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($post->id, $comment->post->id);
    }
}
