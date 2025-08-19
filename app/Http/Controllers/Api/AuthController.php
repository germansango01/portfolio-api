<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\BaseController;

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
            return $this->sendError(__('auth.failed'), 401);
        }
        
        // Authentication passed, retrieve the user
        $user = Auth::user();
        // If authentication is successful, generate a token
        $token = $user->createToken('API Token')->accessToken;

        return $this->sendData(['token' => $token, 'user' => $user], __('auth.success_login'));
    }

}
