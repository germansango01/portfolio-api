<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Tests\TestCase;

class BaseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected BaseController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new BaseController();
    }

    public function test_send_data(): void
    {
        $response = $this->controller->sendData(['foo' => 'bar'], 'Test message');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => 'Test message',
            'data' => ['foo' => 'bar'],
        ], $response->getData(true));
    }

    public function test_send_data_without_message(): void
    {
        $response = $this->controller->sendData(['foo' => 'bar']);

        $this->assertEquals([
            'success' => true,
            'message' => '',
            'data' => ['foo' => 'bar'],
        ], $response->getData(true));
    }

    public function test_send_success(): void
    {
        $response = $this->controller->sendSuccess('Test message');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => 'Test message',
        ], $response->getData(true));
    }

    public function test_send_error(): void
    {
        $response = $this->controller->sendError('Test error', Response::HTTP_BAD_REQUEST);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Test error',
        ], $response->getData(true));
    }

    public function test_send_error_with_default_code(): void
    {
        $response = $this->controller->sendError('Default error');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function test_send_created_with_custom_message(): void
    {
        $response = $this->controller->sendCreated('Custom created message');

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => 'Custom created message',
        ], $response->getData(true));
    }

    public function test_send_created_with_default_message(): void
    {
        $response = $this->controller->sendCreated();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function test_send_no_content(): void
    {
        $response = $this->controller->sendNoContent();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }
}
