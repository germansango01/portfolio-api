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

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => 'Success message',
        ], $response->getData(true));
    }

    public function testSendError()
    {
        $response = $this->controller->sendError('Error message');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Error message',
        ], $response->getData(true));
    }

    public function testSendData()
    {
        $data = ['key' => 'value'];
        $response = $this->controller->sendData($data, 'Data message');

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => 'Data message',
            'data' => $data,
        ], $response->getData(true));
    }

    public function testSendUnauthorized()
    {
        $response = $this->controller->sendUnauthorized();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Unauthorized',
        ], $response->getData(true));
    }

    public function testSendForbidden()
    {
        $response = $this->controller->sendForbidden();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Forbidden',
        ], $response->getData(true));
    }

    public function testSendNotFound()
    {
        $response = $this->controller->sendNotFound();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Not Found',
        ], $response->getData(true));
    }

    public function testSendInternalError()
    {
        $response = $this->controller->sendInternalError();

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Internal Server Error',
        ], $response->getData(true));
    }

    public function testSendCreated()
    {
        $response = $this->controller->sendCreated('Created successfully');

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => 'Created successfully',
        ], $response->getData(true));
    }

    public function testSendNoContent()
    {
        $response = $this->controller->sendNoContent();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }
}
