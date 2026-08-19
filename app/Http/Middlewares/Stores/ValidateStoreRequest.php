<?php

namespace App\Http\Middlewares\Stores;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ValidateStoreRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $isUpdate = $request->isMethod('PUT');
        $storeId  = $request->route('id');

        $rules = [
            'store_name'    => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'address'       => ['required', 'string', 'max:255'],
            'owner_name'    => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:50'],
            'email'         => [
                'required',
                'email',
                $isUpdate
                    ? Rule::unique('stores')->ignore($storeId)
                    : Rule::unique('stores'),
            ],
            'admin_redirect_link'    => ['nullable', 'string', 'max:255'],
            'customer_redirect_link' => ['nullable', 'string', 'max:255'],
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
