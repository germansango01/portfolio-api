<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
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
            'password' => $request->password,
        ]);

        $token = $user->createToken('API Token')->accessToken;

        return $this->sendData(
            ['token' => $token],
            __('auth.success_register'),
        );
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
            return $this->sendError(__('auth.failed'));
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            return $this->sendError(__('auth.email_not_verified'), 403);
        }

        $token = $user->createToken('API Token')->accessToken;

        return $this->sendData(['token' => $token], __('auth.success_login'));
    }

    public function verify(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->route('id'));

        // Comprueba si el usuario ya ha verificado su email
        if ($user->hasVerifiedEmail()) {
            return $this->sendData([], __('auth.email_already_verified'));
        }

        // Verifica el email
        if ($user->markEmailAsVerified()) {
            // Dispara el evento 'Verified'
            event(new Verified($user));
        }

        return $this->sendSuccess(__('auth.email_verified_successfully'));
    }

    /**
     * Reenvía el email de verificación.
     * El usuario debe autenticarse o pasar email (vamos por seguridad: autenticado).
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link resent.']);
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
