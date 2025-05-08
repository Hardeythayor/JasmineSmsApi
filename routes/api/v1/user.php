<?php

use App\Http\Controllers\User\Auth\LoginController;
use App\Http\Controllers\User\Auth\RegisterController;
use App\Http\Controllers\User\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
});

Route::group(['middleware' => ['auth:user']], function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::patch('/profile/change_password', [ProfileController::class, 'changePassword']);
});