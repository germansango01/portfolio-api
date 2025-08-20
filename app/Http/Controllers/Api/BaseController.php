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
     * Send a validation error response.
     */
    public function sendValidationError(array $errors, string $message = 'Validation Error', int $code = Response::HTTP_UNPROCESSABLE_ENTITY): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send an unauthorized response.
     */
    public function sendUnauthorized(string $message = 'Unauthorized', int $code = Response::HTTP_UNAUTHORIZED): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send a forbidden response.
     */
    public function sendForbidden(string $message = 'Forbidden', int $code = Response::HTTP_FORBIDDEN): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send a not found response.
     */
    public function sendNotFound(string $message = 'Not Found', int $code = Response::HTTP_NOT_FOUND): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send an internal server error response.
     */
    public function sendInternalError(string $message = 'Internal Server Error', int $code = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send a created response.
     */
    public function sendCreated(string $message = 'Resource Created', int $code = Response::HTTP_CREATED): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send a no content response.
     */
    public function sendNoContent(): Response
    {
        return response('', Response::HTTP_NO_CONTENT);
    }
}
