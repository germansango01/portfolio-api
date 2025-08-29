<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    /**
     * Handle user login.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->sendError(__('auth.failed'));
        }

        $user = Auth::user();
        $token = $user->createToken('API Token')->accessToken;

        return $this->sendData(['token' => $token], __('auth.success_login'));
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return $this->sendSuccess(__('auth.success_logout'));
    }

    /**
     * Retrieve authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = (new UserResource($request->user()))->resolve();

        return $this->sendData(['user' => $user], __('auth.user_retrieved'));
    }
}
