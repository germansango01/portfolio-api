<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\SearchRequest;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SearchRequestTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_q_is_required()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => null], $rules);
        $this->assertFalse($validator->passes());
    }

    public function test_q_is_string()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 123], $rules);
        $this->assertFalse($validator->passes());
    }

    public function test_q_has_max_length_100()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => str_repeat('a', 101)], $rules);
        $this->assertFalse($validator->passes());

        $validator = Validator::make(['q' => str_repeat('a', 100)], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_category_is_nullable()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'category' => null], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_category_must_exist_in_categories_table()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'category' => 999], $rules);
        $this->assertFalse($validator->passes());

        $category = Category::factory()->create();
        $validator = Validator::make(['q' => 'test', 'category' => $category->id], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_author_is_nullable()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'author' => null], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_author_must_exist_in_users_table()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'author' => 999], $rules);
        $this->assertFalse($validator->passes());

        $user = User::factory()->create();
        $validator = Validator::make(['q' => 'test', 'author' => $user->id], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_tag_is_nullable()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'tag' => null], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_tag_must_exist_in_tags_table()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'tag' => 999], $rules);
        $this->assertFalse($validator->passes());

        $tag = Tag::factory()->create();
        $validator = Validator::make(['q' => 'test', 'tag' => $tag->id], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_page_is_nullable()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'page' => null], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_page_is_integer()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'page' => 'abc'], $rules);
        $this->assertFalse($validator->passes());
    }

    public function test_page_min_value_is_1()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'page' => 0], $rules);
        $this->assertFalse($validator->passes());

        $validator = Validator::make(['q' => 'test', 'page' => 1], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_per_page_is_nullable()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'per_page' => null], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_per_page_is_integer()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'per_page' => 'abc'], $rules);
        $this->assertFalse($validator->passes());
    }

    public function test_per_page_min_value_is_1()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'per_page' => 0], $rules);
        $this->assertFalse($validator->passes());

        $validator = Validator::make(['q' => 'test', 'per_page' => 1], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_per_page_max_value_is_50()
    {
        $rules = (new SearchRequest())->rules();
        $validator = Validator::make(['q' => 'test', 'per_page' => 51], $rules);
        $this->assertFalse($validator->passes());

        $validator = Validator::make(['q' => 'test', 'per_page' => 50], $rules);
        $this->assertTrue($validator->passes());
    }
}
