<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BaseController extends Controller
{
    /**
     * Send a response with data.
     */
    public function sendData(array $data, string $message = '', int $code = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send a success response.
     */
    public function sendSuccess(string $message, int $code = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send an error response.
     */
    public function sendError(string $error, int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send a created response.
     */
    public function sendCreated(string $message = 'Resource Created', int $code = Response::HTTP_CREATED): JsonResponse
    {
        return $this->sendSuccess($message, $code);
    }

    /**
     * Send a no content response.
     */
    public function sendNoContent(): Response
    {
        return response('', Response::HTTP_NO_CONTENT);
    }
}
