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
        return $this->loginService->login($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->loginService->logout($request);
    }
}
