<?php

use App\Http\Controllers\Login\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
