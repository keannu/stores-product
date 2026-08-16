<?php

use App\Http\Controllers\Login\LoginController;
use App\Http\Middlewares\Dashboard\RedirectIfNotAuthenticated;
use App\Http\Middlewares\Login\ValidateLoginRequest;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login'])->middleware(ValidateLoginRequest::class);
Route::post('/logout', [LoginController::class, 'logout']);

Route::get('/dashboard/{any?}', function () {
    return view('dashboard');
})->middleware(RedirectIfNotAuthenticated::class)->where('any', '.*');

Route::get('/{any}', function () {
    return view('login');
})->where('any', '.*');
