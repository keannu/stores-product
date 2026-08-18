<?php

namespace App\Services\Login;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 3600; // 1 hour

    private function throttleKey(Request $request): string
    {
        return 'login:' . strtolower($request->input('email'));
    }

    public function login(Request $request): JsonResponse
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return response()->json([
                'message' => "Account locked due to too many failed attempts. Try again in {$minutes} minute(s).",
            ], 429);
        }

        if (!User::where('email', $request->input('email'))->exists()) {
            return response()->json([
                'message' => 'No account found with that email address.',
            ], 404);
        }

        if (!Auth::attempt($request->only(['email', 'password']))) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);

            $remaining = self::MAX_ATTEMPTS - RateLimiter::attempts($key);

            if ($remaining > 0) {
                return response()->json([
                    'message' => "Invalid credentials. {$remaining} attempt(s) remaining before account is locked.",
                ], 401);
            }

            return response()->json([
                'message' => 'Account locked due to too many failed attempts. Try again in 1 hour.',
            ], 429);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $user = Auth::user();

        $request->session()->put('auth_user_id', $user->id);
        $request->session()->put('auth_user_role', $user->role);
        $request->session()->put('auth_user_store_id', $user->store_id);

        $redirect = '/dashboard';

        if ($user->role === 'admin') {
            $redirect = $user->store?->admin_redirect_link ?: '/dashboard';
        } elseif ($user->role === 'customer') {
            $redirect = $user->store?->customer_redirect_link ?: '/';
        }

        return response()->json([
            'message'  => 'Login successful',
            'redirect' => $redirect,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }
}
