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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

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
     * @OA\Response(response=200, description="User registered successfully, pending email verification.",
     *   @OA\JsonContent(
     *     @OA\Property(property="success", type="boolean", example=true),
     *     @OA\Property(property="message", type="string", example="Registration successful. Check your email for a verification link.")
     *   )
     * ),
     * @OA\Response(response=422, description="Validation error.",
     *   @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
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
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LoginRequest")),
     * @OA\Response(response=200, description="User logged in successfully.",
     *   @OA\JsonContent(
     *     @OA\Property(property="success", type="boolean", example=true),
     *     @OA\Property(property="message", type="string", example="Login successful."),
     *     @OA\Property(property="data", type="object",
     *       @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6Ijc1N...")
     *     )
     *   )
     * ),
     * @OA\Response(response=401, description="Unauthorized: Invalid credentials.",
     *   @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")
     * ),
     * @OA\Response(response=403, description="Forbidden: Email not verified.",
     *   @OA\JsonContent(ref="#/components/schemas/ForbiddenResponse")
     * )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return $this->sendUnauthenticated(__('auth.failed'));
        }

        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            return $this->sendForbidden(__('auth.email_not_verified'));
        }

        $token = $user->createToken('API Token')->accessToken;

        return $this->sendData(['token' => $token], __('auth.success_login'));
    }

    /**
     * @OA\Get(
     * path="/api/v1/email/verify/{id}/{hash}",
     * summary="Verify user's email address",
     * tags={"Auth"},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Parameter(name="hash", in="path", required=true, @OA\Schema(type="string")),
     * @OA\Response(response=302, description="Redirects to frontend with status"),
     * @OA\Response(response=404, description="User not found.")
     * )
     */
    public function verify(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return redirect(env('FRONTEND_VERIFICATION_URL') . '?status=already-verified');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect(env('FRONTEND_VERIFICATION_URL') . '?status=success');
    }

    /**
     * @OA@Post(
     * path="/api/v1/email/resend",
     * summary="Resend the email verification link",
     * tags={"Auth"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Verification link resent successfully."),
     * @OA\Response(response=400, description="Email already verified."),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->sendUnauthenticated(__('auth.unauthenticated'));
        }

        if ($user->hasVerifiedEmail()) {
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
     * @OA\Response(response=200, description="User logged out successfully."),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
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
     *   @OA\Property(property="success", type="boolean", example=true),
     *   @OA\Property(property="message", type="string", example="User retrieved successfully."),
     *   @OA\Property(property="data", type="object",
     *     @OA\Property(property="user", ref="#/components/schemas/User"),
     *     @OA\Property(property="roles", type="array", @OA\Items(type="string", example="admin"))
     *   )
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated")
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

    /**
     * @OA\Post(
     * path="/api/v1/password/forgot",
     * summary="Request password reset link",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     *   @OA\Property(property="email", type="string", format="email", example="user@example.com")
     * )
     * ),
     * @OA\Response(response=200, description="Password reset link sent successfully"),
     * @OA\Response(response=400, description="Failed to send password reset link")
     * )
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return $this->sendSuccess(__('auth.password_reset_link_sent'));
        }

        return $this->sendError(__('auth.password_reset_link_failed'), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @OA\Post(
     * path="/api/v1/password/reset",
     * summary="Reset user password and return API token",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     *   @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *   @OA\Property(property="token", type="string", example="reset-token"),
     *   @OA\Property(property="password", type="string", format="password", example="newpassword"),
     *   @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword")
     * )
     * ),
     * @OA\Response(response=200, description="Password reset successful with token"),
     * @OA\Response(response=400, description="Password reset failed")
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            $user = User::where('email', $request->email)->first();
            $token = $user->createToken('API Token')->accessToken;

            return $this->sendData(['token' => $token], __('auth.password_reset_success'));
        }

        return $this->sendError(__('auth.password_reset_failed'), Response::HTTP_BAD_REQUEST);
    }
}
