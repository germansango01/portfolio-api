<?php

namespace Tests\Feature;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseFormRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorize_returns_true()
    {
        $request = new class extends BaseFormRequest {
            public function rules(): array
            {
                return []; // Not relevant for this test
            }
        };

        $this->assertTrue($request->authorize());
    }
}
