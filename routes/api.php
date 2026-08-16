<?php

use App\Http\Controllers\Users\UserController;
use App\Http\Middlewares\Dashboard\RedirectIfNotAuthenticated;
use App\Http\Middlewares\Users\ValidateUserRequest;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/stores', [UserController::class, 'stores'])
    ->middleware(RedirectIfNotAuthenticated::class);

Route::get('/dashboard/users', [UserController::class, 'index'])
    ->middleware(RedirectIfNotAuthenticated::class);

Route::post('/dashboard/users', [UserController::class, 'store'])
    ->middleware([RedirectIfNotAuthenticated::class, ValidateUserRequest::class]);

Route::put('/dashboard/users/{id}', [UserController::class, 'update'])
    ->middleware([RedirectIfNotAuthenticated::class, ValidateUserRequest::class]);

Route::delete('/dashboard/users/{id}', [UserController::class, 'destroy'])
    ->middleware(RedirectIfNotAuthenticated::class);
