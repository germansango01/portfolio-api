<?php

namespace Tests\Feature\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Response;
use Tests\TestCase;

class BaseControllerTest extends TestCase
{
    protected BaseController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new BaseController();
    }

    public function testSendSuccess()
    {
        $response = $this->controller->sendSuccess('Success message');

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals([
            'success' => true,
            'message' => 'Success message',
        ], $response->getData(true));
    }

    public function testSendSuccess1()
{
    $response = $this->controller->sendSuccess('Success message');

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertEquals([
        'success' => true,
        'message' => 'Success message',
    ], $response->getData(true));
}

    public function testSendError()
    {
        $response = $this->controller->sendError('Error message');

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertEquals([
            'success' => false,
            'message' => 'Error message',
        ], $response->getData(true));
    }

    public function testSendData()
    {
        $data = ['key' => 'value'];
        $response = $this->controller->sendData($data, 'Data message');

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals([
            'success' => true,
            'message' => 'Data message',
            'data' => $data,
        ], $response->getData(true));
    }

    public function testSendValidationError()
    {
        $errors = ['field' => ['The field is required']];
        $response = $this->controller->sendValidationError($errors);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals([
            'success' => false,
            'message' => 'Validation Error',
            'errors' => $errors,
        ], $response->getData(true));
    }

    public function testSendUnauthorized()
    {
        $response = $this->controller->sendUnauthorized();

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertEquals([
            'success' => false,
            'message' => 'Unauthorized',
        ], $response->getData(true));
    }

    public function testSendForbidden()
    {
        $response = $this->controller->sendForbidden();

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertEquals([
            'success' => false,
            'message' => 'Forbidden',
        ], $response->getData(true));
    }

    public function testSendNotFound()
    {
        $response = $this->controller->sendNotFound();

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $this->assertEquals([
            'success' => false,
            'message' => 'Not Found',
        ], $response->getData(true));
    }

    public function testSendInternalError()
    {
        $response = $this->controller->sendInternalError();

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals([
            'success' => false,
            'message' => 'Internal Server Error',
        ], $response->getData(true));
    }

    public function testSendCreated()
    {
        $response = $this->controller->sendCreated('Created successfully');

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertEquals([
            'success' => true,
            'message' => 'Created successfully',
        ], $response->getData(true));
    }

    public function testSendNoContent()
    {
        $response = $this->controller->sendNoContent();

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($response->getContent());
    }
}
