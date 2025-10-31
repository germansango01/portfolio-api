<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): User
    {
        $user = User::factory()->create();
        Passport::actingAs($user, [], 'api');
        return $user;
    }

    public function test_index_returns_a_list_of_tags(): void
    {
        $this->authenticate();

        Tag::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/tags');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'tags',
                ],
            ])
            ->assertJsonCount(3, 'data.tags');
    }

    public function test_show_returns_a_single_tag(): void
    {
        $this->authenticate();

        $tag = Tag::factory()->create();

        $response = $this->getJson('/api/v1/tag/' . $tag->slug);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'tag',
                ],
            ])
            ->assertJson([
                'data' => [
                    'tag' => [
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    ],
                ],
            ]);
    }

    public function test_show_returns_a_404_error_if_the_tag_does_not_exist(): void
    {
        $this->authenticate();

        $response = $this->getJson('/api/v1/tags/non-existent-slug');

        $response->assertNotFound();
    }
}
