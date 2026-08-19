<?php

namespace App\Http\Middlewares\Stores;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictToSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()?->role !== 'super_admin') {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'You are not allowed to access this resource.'], 403);
            }

            return redirect('/dashboard');
        }

        return $next($request);
    }
}
