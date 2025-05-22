<?php

use App\Http\Controllers\User\Auth\LoginController;
use App\Http\Controllers\User\Auth\RegisterController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\SMSController;
use App\Http\Controllers\User\UserCreditController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::match(['get', 'post'], '/webhook', [SMSController::class, 'webhookResponse']);
Route::get('/run', [SMSController::class, 'runArtisanCommand']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
});

Route::group(['middleware' => ['auth:user']], function () {
    Route::post('/auth/logout', [LoginController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'fetchUserProfile']);
    Route::patch('/profile/change_password', [ProfileController::class, 'changePassword']);
    Route::prefix('credit')->group(function() {
        Route::get('/{user_id?}', [UserCreditController::class, 'fetchUserCredit']);
        Route::post('/history/{user_id?}', [UserCreditController::class, 'fetchUserCreditHistory']);
    });
    Route::prefix('message')->group(function() {
        Route::post('/send', [SMSController::class, 'sendMessage']);
    });
});