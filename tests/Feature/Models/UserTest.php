<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_be_created()
    {
        $user = User::factory()->create();
        $this->assertNotNull($user);
        $this->assertDatabaseHas('users', ['email' => $user->email]);
    }

    public function test_user_has_fillable_attributes()
    {
        $user = new User([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(password_verify('password', $user->password));
    }

    public function test_user_has_hidden_attributes()
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_user_password_is_hashed()
    {
        $password = 'secret';
        $user = User::factory()->create(['password' => $password]);

        $this->assertTrue(password_verify($password, $user->password));
    }

    public function test_user_has_many_posts()
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->posts);
        $this->assertCount(3, $user->posts);
    }

    public function test_user_has_many_comments()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        Comment::factory()->count(2)->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->comments);
        $this->assertCount(2, $user->comments);
    }
}
