<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Tests\TestCase;

class BaseControllerTest extends TestCase
{
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new BaseController();
    }

    public function test_send_data_formats_response_correctly()
    {
        $response = $this->controller->sendData(['key' => 'value'], 'Test Message', 201);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => 'Test Message',
            'data' => ['key' => 'value'],
        ], $response->getData(true));
    }

    public function test_send_success_formats_response_correctly()
    {
        $response = $this->controller->sendSuccess('Success Message', 202);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => 'Success Message',
        ], $response->getData(true));
    }

    public function test_send_error_formats_response_correctly()
    {
        $response = $this->controller->sendError('Error Message', 400);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Error Message',
        ], $response->getData(true));
    }

    public function test_send_error_includes_errors_array_when_provided()
    {
        $errors = ['field' => ['The field is required.']];
        $response = $this->controller->sendError('Validation Failed', 422, $errors);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Validation Failed',
            'errors' => $errors,
        ], $response->getData(true));
    }

    public function test_send_not_found_uses_correct_status_code()
    {
        $response = $this->controller->sendNotFound('Resource Not Found');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Resource Not Found',
        ], $response->getData(true));
    }

    public function test_send_forbidden_uses_correct_status_code()
    {
        $response = $this->controller->sendForbidden('Access Denied');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Access Denied',
        ], $response->getData(true));
    }

    public function test_send_unauthenticated_uses_correct_status_code()
    {
        $response = $this->controller->sendUnauthenticated('Auth Required');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Auth Required',
        ], $response->getData(true));
    }

    public function test_send_validation_error_formats_response_correctly()
    {
        $errors = ['email' => ['Invalid email format.']];
        $response = $this->controller->sendValidationError('Invalid Data', $errors);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Invalid Data',
            'errors' => $errors,
        ], $response->getData(true));
    }

    public function test_send_created_uses_correct_status_code_and_default_message()
    {
        // Mock the translation function
        $this->app->instance('translator', new class extends \Illuminate\Translation\Translator {
            public function __construct() {}
            public function get($key, array $replace = [], $locale = null, $fallback = true) {
                return 'messages.resource_created';
            }
        });

        $response = $this->controller->sendCreated();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => __('messages.resource_created'),
        ], $response->getData(true));
    }

    public function test_send_no_content_returns_correct_response()
    {
        $response = $this->controller->sendNoContent();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }
}
