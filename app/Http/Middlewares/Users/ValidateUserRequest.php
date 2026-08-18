<?php

namespace App\Http\Middlewares\Users;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $isUpdate = $request->isMethod('PUT');
        $userId   = $request->route('id');

        $allowedRoles = Auth::user()?->role === 'super_admin'
            ? ['super_admin', 'admin', 'customer']
            : ['admin', 'customer'];

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => [
                'required',
                'email',
                $isUpdate
                    ? Rule::unique('users')->ignore($userId)
                    : Rule::unique('users'),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role'     => ['required', Rule::in($allowedRoles)],
            'store_id' => [
                $request->input('role') === 'super_admin' ? 'nullable' : 'required',
                'exists:stores,id',
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return $next($request);
    }
}
