<?php

use App\Http\Controllers\Stores\StoreController;
use App\Http\Controllers\Users\UserController;
use App\Http\Middlewares\Dashboard\RedirectIfNotAuthenticated;
use App\Http\Middlewares\Stores\ValidateStoreRequest;
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

        // Stores CRUD
        Route::get('/dashboard/stores/list', [StoreController::class, 'index']);

        Route::post('/dashboard/stores', [StoreController::class, 'store'])
            ->middleware(ValidateStoreRequest::class);

        Route::put('/dashboard/stores/{id}', [StoreController::class, 'update'])
            ->middleware(ValidateStoreRequest::class);

        Route::delete('/dashboard/stores/{id}', [StoreController::class, 'destroy']);

    });
