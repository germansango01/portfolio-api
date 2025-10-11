<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    /**
     * @OA\Post(
     * path="/api/v1/register",
     * summary="Register a new user",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     * ),
     * @OA\Response(
     * response=200,
     * description="User registered successfully, pending email verification.",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Registration successful. Check your email for a verification link.")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error.",
     * @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     * )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->sendEmailVerificationNotification();

        return $this->sendSuccess(__('auth.success_register_pending_verification'));
    }

    /**
     * @OA\Post(
     * path="/api/v1/login",
     * summary="Login a user and return the API token",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     * ),
     * @OA\Response(
     * response=200,
     * description="User logged in successfully.",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Login successful."),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6Ijc1N...")
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized: Invalid credentials.",
     * @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden: Email not verified.",
     * @OA\JsonContent(ref="#/components/schemas/ForbiddenResponse")
     * )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            // Usamos el método semántico sendUnauthenticated (401)
            return $this->sendUnauthenticated(__('auth.failed'));
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            // Usamos el método semántico sendForbidden (403)
            return $this->sendForbidden(__('auth.email_not_verified'));
        }

        $token = $user->createToken('API Token')->accessToken;

        return $this->sendData(['token' => $token], __('auth.success_login'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/email/verify/{id}/{hash}",
     *     summary="Verify user's email address",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         description="Verification hash",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirects to the frontend with a status query parameter."
     *     ),
     *      @OA\Response(
     *         response=404,
     *         description="User not found."
     *     )
     * )
     */
    // El tipo de retorno ahora incluye RedirectResponse
    public function verify(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->route('id'));

        // 1. Manejar el caso de 'ya verificado'
        if ($user->hasVerifiedEmail()) {
            // Redirigir al frontend con un indicador de estado
            return redirect(env('FRONTEND_VERIFICATION_URL') . '?status=already-verified');
        }

        // 2. Realizar la verificación
        $user->markEmailAsVerified();
        event(new Verified($user));

        // 3. Redirigir al frontend con un indicador de éxito
        return redirect(env('FRONTEND_VERIFICATION_URL') . '?status=success');
    }

    /**
     * @OA\Post(
     * path="/api/v1/email/resend",
     * summary="Resend the email verification link to the authenticated user",
     * tags={"Auth"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Verification link resent successfully."
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated: User is not logged in."
     * ),
     * @OA\Response(
     * response=400,
     * description="Bad Request: Email is already verified."
     * )
     * )
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            // Usamos sendUnauthenticated (401) si el middleware falla
            return $this->sendUnauthenticated(__('auth.unauthenticated'));
        }

        if ($user->hasVerifiedEmail()) {
            // Usamos sendError con 400 Bad Request
            return $this->sendError(__('auth.email_already_verified'), Response::HTTP_BAD_REQUEST);
        }

        $user->sendEmailVerificationNotification();

        return $this->sendSuccess(__('auth.verification_link_resent'));
    }

    /**
     * @OA\Post(
     * path="/api/v1/logout",
     * summary="Logout a user by revoking the current access token",
     * tags={"Auth"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="User logged out successfully."
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated: No token provided or token invalid."
     * )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        // Se asume que el usuario existe debido al middleware de autenticación
        $request->user()->token()->revoke();

        return $this->sendSuccess(__('auth.success_logout'));
    }

    /**
     * @OA\Get(
     * path="/api/v1/user",
     * summary="Get authenticated user details",
     * tags={"Auth"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="User retrieved successfully.",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="User retrieved successfully."),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="user", ref="#/components/schemas/User"),
     * @OA\Property(property="roles", type="array", @OA\Items(type="string", example="admin"))
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated: No token provided or token invalid."
     * )
     * )
     */
    public function user(Request $request): JsonResponse
    {
        $user = Auth::user();
        $roles = $user->getRoleNames();

        return $this->sendData([
            'user' => UserResource::make($user)->resolve(),
            'roles' => $roles,
        ], __('auth.user_retrieved'));
    }
}
