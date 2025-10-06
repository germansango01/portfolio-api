<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error."
     *     )
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
     *     path="/api/login",
     *     summary="Login a user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully."
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized."
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->sendError(__('auth.failed'), 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            return $this->sendError(__('auth.email_not_verified'), 403);
        }

        $token = $user->createToken('API Token')->accessToken;

        return $this->sendData(['token' => $token], __('auth.success_login'));
    }

    /**
     * Verifica el email desde un enlace firmado
     * Ruta: GET /api/email/verify/{id}/{hash}
     * Middleware: auth:api, signed
     */
    public function verifySigned(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();

        return $this->sendSuccess(__('auth.email_verified_successfully'));
    }

    /**
     * Verifica el email manualmente (sin firma)
     * Ruta: POST /api/email/verify
     * Body: { "id": 1 }
     */
    public function verify(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->input('id'));

        if ($user->hasVerifiedEmail()) {
            return $this->sendData([], __('auth.email_already_verified'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->sendSuccess(__('auth.email_verified_successfully'));
    }

    /**
     * Reenvía el email de verificación
     * Ruta: POST /api/email/resend
     * Middleware: auth:api
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->sendError(__('auth.unauthenticated'), 401);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->sendError(__('auth.email_already_verified'), 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->sendSuccess(__('auth.verification_link_resent'));
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout a user",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully."
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return $this->sendSuccess(__('auth.success_logout'));
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get authenticated user",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     )
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
