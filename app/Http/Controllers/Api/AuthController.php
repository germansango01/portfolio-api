<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    /**
     * @OA\Post(
     * path="/api/v1/register",
     * summary="Register a new user",
     * tags={"Auth"},
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RegisterRequest")),
     * @OA\Response(response=200, description="User registered successfully, pending email verification")
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
     * summary="Login a user and return API token",
     * tags={"Auth"},
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LoginRequest")),
     * @OA\Response(response=200, description="Login successful with token")
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
     * @OA\Response(response=302, description="Redirects to frontend")
     * )
     */
    public function verify(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return redirect(env('FRONTEND_AUTH_URL') . '/verify?status=already-verified');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect(env('FRONTEND_AUTH_URL') . '/verify?status=success');
    }

    /**
     * @OA\Post(
     * path="/api/v1/email/resend",
     * summary="Resend email verification",
     * tags={"Auth"},
     * security={{"bearerAuth":{}}}
     * )
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->sendUnauthenticated(__('auth.unauthenticated'));
        }

        if ($user->hasVerifiedEmail()) {
            return $this->sendError(__('auth.email_already_verified'), 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->sendSuccess(__('auth.verification_link_resent'));
    }

    /**
     * @OA\Post(
     * path="/api/v1/email/resend-guest",
     * summary="Resend email verification link (public: requires only email)",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="email", type="string", format="email", example="unverified@example.com")
     * )
     * ),
     * @OA\Response(response=200, description="Returns success message if email is valid, regardless of whether user exists or not, to prevent user enumeration."),
     * @OA\Response(response=400, description="Email already verified.",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Email already verified.")
     * )
     * ),
     * @OA\Response(response=422, description="Validation error.")
     * )
     */
    public function resendVerificationLink(Request $request): JsonResponse
    {
        // 1. Validación simple para asegurar que el formato del email es correcto
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // 2. Si el usuario no existe, devolvemos éxito para evitar enumeración de usuarios
        if (! $user) {
            return $this->sendSuccess(__('auth.verification_link_sent_if_unverified'));
        }

        // 3. Si ya está verificado, devolvemos un error
        if ($user->hasVerifiedEmail()) {
            return $this->sendError(__('auth.email_already_verified'), Response::HTTP_BAD_REQUEST);
        }

        // 4. Reenviar correo
        $user->sendEmailVerificationNotification();

        return $this->sendSuccess(__('auth.verification_link_resent'));
    }

    /**
     * @OA\Post(
     * path="/api/v1/logout",
     * summary="Logout user and revoke token",
     * tags={"Auth"},
     * security={{"bearerAuth":{}}}
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
     * summary="Get authenticated user",
     * tags={"Auth"},
     * security={{"bearerAuth":{}}}
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
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ForgotPasswordRequest")),
     * @OA\Response(response=200, description="Password reset link sent successfully"),
     * @OA\Response(response=400, description="Failed to send password reset link")
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->sendError(__('auth.password_reset_link_failed'), 400);
        }

        $token = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => now()],
        );

        $user->notify(new ResetPasswordNotification($token, $user->email));

        return $this->sendSuccess(__('auth.password_reset_link_sent'));
    }

    /**
     * @OA\Post(
     * path="/api/v1/password/reset",
     * summary="Reset user password and return API token",
     * tags={"Auth"},
     * @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ResetPasswordRequest")),
     * @OA\Response(response=200, description="Password reset successful with token"),
     * @OA\Response(response=400, description="Password reset failed")
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $record = DB::table('password_resets')->where('email', $request->email)->first();

        if (! $record || $request->token !== $record->token) {
            return $this->sendError(__('auth.password_reset_failed'), 400);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->sendError(__('auth.password_reset_failed'), 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        $token = $user->createToken('API Token')->accessToken;

        return $this->sendData(['token' => $token], __('auth.password_reset_success'));
    }
}
