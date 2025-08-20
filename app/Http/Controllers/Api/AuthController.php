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
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Validate the request using the LoginRequest rules
        if (!Auth::attempt($request->only('email', 'password'))) {
            // If authentication fails, return an error response
            return $this->sendError(__('auth.failed'));
        }

        // Authentication passed, retrieve the user
        $user = Auth::user();
        // If authentication is successful, generate a token
        $token = $user->createToken('API Token')->accessToken;

        return $this->sendData(['token' => $token], __('auth.success_login'));
    }

    public function logout(Request $request): JsonResponse
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->token()->revoke();

        return $this->sendSuccess(__('auth.success_logout'));
    }

    /**
     * Undocumented function.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        $user = new UserResource($request->user());

        return $this->sendData(['user' => $user], __('auth.user_retrieved'));
    }
}
