<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InviteCodeController;
use App\Http\Controllers\Admin\ManageSmsGatewayController;
use App\Http\Controllers\Admin\ManageUserController;
use App\Http\Controllers\Admin\ThirdPartyController;
use App\Http\Middleware\AdminAccessOnly;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:user', AdminAccessOnly::class]], function () {
    Route::prefix('users')->group(function() {
        Route::post('/', [ManageUserController::class, 'fetchUsers']);
        Route::get('profile/{id}', [ManageUserController::class, 'fetchUserProfile']);
        Route::patch('change_status/{id}', [ManageUserController::class, 'toggleUserStatus']);
        Route::post('sms_credit/{id}', [ManageUserController::class, 'addUserSmsCredit']);
        Route::patch('reset_password/{id}', [ManageUserController::class, 'resetUserPassword']);
    });
    Route::prefix('sms_gateway')->group(function() {
        Route::get('/', [ManageSmsGatewayController::class, 'fetchSmsGateway']);
        Route::patch('activate/{id}', [ManageSmsGatewayController::class, 'activateSmsGateway']);
        Route::patch('charge/{id}', [ManageSmsGatewayController::class, 'updateSmsGatewayCharge']);
    });
    Route::prefix('thirdparty')->group(function() {
        Route::get('numbers', [ThirdPartyController::class, 'fetchThirdPartyNumbers']);
        Route::post('numbers', [ThirdPartyController::class, 'addThirdPartyNumber']);
        Route::put('numbers/{id}', [ThirdPartyController::class, 'editThirdPartyNumber']);
    });
    Route::prefix('invite_code')->group(function() {
        Route::get('/', [InviteCodeController::class, 'fetchInviteCodes']);
        Route::post('/', [InviteCodeController::class, 'addInviteCode']);
        Route::put('/{id}', [InviteCodeController::class, 'editInviteCode']);
    });
    Route::prefix('dashboard')->group(function() {
        Route::get('/analytics', [DashboardController::class, 'fetchAnalytics']);
        Route::get('/chart_data', [DashboardController::class, 'fetchSmsChartData']);
    });
});