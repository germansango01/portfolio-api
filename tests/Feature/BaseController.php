<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class BaseController extends TestCase
{
    protected BaseController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new self();
    }

    public function test_send_data_returns_json()
    {
        $data = ['foo' => 'bar'];
        $message = 'Data retrieved';

        $response = $this->controller->sendData($data, $message);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson([
                     'success' => true,
                     'message' => $message,
                     'data' => $data,
                 ]);
    }

    public function test_send_success_returns_json()
    {
        $message = 'Operation successful';

        $response = $this->controller->sendSuccess($message);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson([
                     'success' => true,
                     'message' => $message,
                 ]);
    }

    public function test_send_error_returns_json()
    {
        $error = 'Something went wrong';

        $response = $this->controller->sendError($error);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                 ->assertJson([
                     'success' => false,
                     'message' => $error,
                 ]);
    }

    public function test_send_validation_error_returns_json()
    {
        $errors = ['field' => ['Field is required']];
        $message = 'Validation Error';

        $response = $this->controller->sendValidationError($errors, $message);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                 ->assertJson([
                     'success' => false,
                     'message' => $message,
                     'errors' => $errors,
                 ]);
    }

    public function test_send_unauthorized_returns_json()
    {
        $response = $this->controller->sendUnauthorized();

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Unauthorized',
                 ]);
    }

    public function test_send_forbidden_returns_json()
    {
        $response = $this->controller->sendForbidden();

        $response->assertStatus(Response::HTTP_FORBIDDEN)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Forbidden',
                 ]);
    }

    public function test_send_not_found_returns_json()
    {
        $response = $this->controller->sendNotFound();

        $response->assertStatus(Response::HTTP_NOT_FOUND)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Not Found',
                 ]);
    }

    public function test_send_internal_error_returns_json()
    {
        $response = $this->controller->sendInternalError();

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Internal Server Error',
                 ]);
    }

    public function test_send_created_returns_json()
    {
        $response = $this->controller->sendCreated();

        $response->assertStatus(Response::HTTP_CREATED)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Resource Created',
                 ]);
    }

    public function test_send_no_content_returns_json()
    {
        $response = $this->controller->sendNoContent();

        $response->assertStatus(Response::HTTP_NO_CONTENT)
                ->assertJson([
                    'success' => true,
                    'message' => 'No Content',
                ]);
    }
}
