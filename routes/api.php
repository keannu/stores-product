<?php

use App\Http\Controllers\Users\UserController;
use App\Http\Middlewares\Dashboard\RedirectIfNotAuthenticated;
use App\Http\Middlewares\Users\ValidatePasswordChangeRequest;
use App\Http\Middlewares\Users\ValidateUserRequest;
use Illuminate\Support\Facades\Route;

Route::middleware(RedirectIfNotAuthenticated::class)
    ->group(function () {

        Route::get('/dashboard/auth-user', [UserController::class, 'authUser']);

        Route::get('/dashboard/stores', [UserController::class, 'stores']);

        Route::get('/dashboard/users', [UserController::class, 'index']);

        Route::post('/dashboard/users', [UserController::class, 'store'])
            ->middleware(ValidateUserRequest::class);

        Route::put('/dashboard/users/{id}', [UserController::class, 'update'])
            ->middleware(ValidateUserRequest::class);

        Route::put('/dashboard/users/{id}/password', [UserController::class, 'changePassword'])
            ->middleware(ValidatePasswordChangeRequest::class);

        Route::delete('/dashboard/users/{id}', [UserController::class, 'destroy']);

    });
