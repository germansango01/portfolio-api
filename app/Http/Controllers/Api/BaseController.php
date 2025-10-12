<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\Info(
 * title="Portfolio API",
 * version="1.0.1",
 * description="API for the portfolio",
 * @OA\Contact(
 * email="support@example.com",
 * name="Support Team"
 * )
 * )
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer"
 * )
 */
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

    // ----------------------------------------------------------------------
    // MÉTODOS DE ERROR ESTANDARIZADOS
    // ----------------------------------------------------------------------

    /**
     * Send an error response.
     * Este es el método base para todos los errores, ahora soporta un array de 'errors'.
     */
    public function sendError(string $error, int $code = Response::HTTP_BAD_REQUEST, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        // Añade el campo 'errors' solo si se proporciona (ideal para 422)
        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Send a not found response (404).
     */
    public function sendNotFound(string $error = 'Resource not found.'): JsonResponse
    {
        return $this->sendError($error, Response::HTTP_NOT_FOUND);
    }

    /**
     * Send an unauthorized (Forbidden) response (403).
     * Usado cuando el usuario está logueado pero no tiene permisos.
     */
    public function sendForbidden(string $error = 'You do not have permission to access this resource.'): JsonResponse
    {
        return $this->sendError($error, Response::HTTP_FORBIDDEN);
    }

    /**
     * Send an unauthenticated response (401).
     * Usado cuando el usuario no ha proporcionado credenciales válidas (no logueado).
     */
    public function sendUnauthenticated(string $error = 'Authentication required.'): JsonResponse
    {
        return $this->sendError($error, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Send a validation error response (422) con el array de errores detallados.
     * Útil para la validación manual dentro de los controladores.
     */
    public function sendValidationError(string $message, array $errors): JsonResponse
    {
        return $this->sendError($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    // ----------------------------------------------------------------------
    // MÉTODOS DE ÉXITO ESPECÍFICOS
    // ----------------------------------------------------------------------

    /**
     * Send a created response.
     */
    public function sendCreated(string $message = '', int $code = Response::HTTP_CREATED): JsonResponse
    {
        if (empty($message)) {
            $message = __('messages.resource_created');
        }

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
