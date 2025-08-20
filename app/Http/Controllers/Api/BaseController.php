<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BaseController extends Controller
{
    /**
     * Send a response with data.
     *
     * @param array $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
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
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
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
     *
     * @param string $error
     * @param int $code
     * @return JsonResponse
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
     *
     * @param array $errors
     * @param string $message
     * @param int $code
     * @return JsonResponse
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
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
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
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
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
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
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
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
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
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
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
     *
     * @return JsonResponse
     */
    public function sendNoContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
