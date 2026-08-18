<?php

namespace App\Http\Middlewares\Users;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ValidatePasswordChangeRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[^a-zA-Z0-9]/',
            ],
        ], [
            'password.min'   => 'Password requirements:',
            'password.regex' => 'Password requirements:',
        ]);

        $validator->after(function ($v) {
            if ($v->errors()->has('password')) {
                $messages = $v->errors()->get('password');
                if (in_array('Password requirements:', $messages)) {
                    $v->errors()->forget('password');
                    $v->errors()->add('password', '• Password must be at least 8 characters long');
                    $v->errors()->add('password', '• Password must contain at least one uppercase letter');
                    $v->errors()->add('password', '• Password must contain at least one lowercase letter');
                    $v->errors()->add('password', '• Password must contain at least one special character');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return $next($request);
    }
}
