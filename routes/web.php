<?php

use App\Http\Controllers\Login\LoginController;
use App\Http\Middlewares\Login\ValidateLoginRequest;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login'])->middleware(ValidateLoginRequest::class);
Route::post('/logout', [LoginController::class, 'logout']);

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/{any}', function () {
    return view('login');
})->where('any', '.*');
