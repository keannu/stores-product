<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Services\Login\LoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(private LoginService $loginService) {}

    public function login(Request $request): JsonResponse
    {
        if (!$this->loginService->login($request->only(['username', 'password']), $request)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json(['message' => 'Login successful']);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->loginService->logout($request);

        return response()->json(['message' => 'Logged out']);
    }
}
