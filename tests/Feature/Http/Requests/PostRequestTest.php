<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\PostRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PostRequestTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_page_is_valid()
    {
        $rules = (new PostRequest())->rules();
        $validator = Validator::make(['page' => 1], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_page_is_not_an_integer()
    {
        $rules = (new PostRequest())->rules();
        $validator = Validator::make(['page' => 'a'], $rules);
        $this->assertFalse($validator->passes());
    }

    public function test_page_is_less_than_1()
    {
        $rules = (new PostRequest())->rules();
        $validator = Validator::make(['page' => 0], $rules);
        $this->assertFalse($validator->passes());
    }

    public function test_per_page_is_valid()
    {
        $rules = (new PostRequest())->rules();
        $validator = Validator::make(['per_page' => 10], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_per_page_is_not_an_integer()
    {
        $rules = (new PostRequest())->rules();
        $validator = Validator::make(['per_page' => 'a'], $rules);
        $this->assertFalse($validator->passes());
    }

    public function test_per_page_is_less_than_1()
    {
        $rules = (new PostRequest())->rules();
        $validator = Validator::make(['per_page' => 0], $rules);
        $this->assertFalse($validator->passes());
    }

    public function test_per_page_is_greater_than_50()
    {
        $rules = (new PostRequest())->rules();
        $validator = Validator::make(['per_page' => 51], $rules);
        $this->assertFalse($validator->passes());
    }

    public function test_page_and_per_page_are_valid()
    {
        $rules = (new PostRequest())->rules();
        $validator = Validator::make(['page' => 1, 'per_page' => 10], $rules);
        $this->assertTrue($validator->passes());
    }

    public function test_page_and_per_page_are_null()
    {
        $rules = (new PostRequest())->rules();
        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->passes());
    }
}
