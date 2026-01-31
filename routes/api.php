<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('users/auth')->group(function () {
        Route::post('/register', [AuthController::class, 'userRegister']);
        Route::post('/verifyOtp', [AuthController::class, 'verifyOtp']);
        Route::post('/resendOtp', [AuthController::class, 'resendOtp']);
        Route::post('/login', [AuthController::class, 'userLogin']);
        Route::post('/forgotPassword', [AuthController::class, 'forgotPassword']);
        Route::post('/resetPassword', [AuthController::class, 'resetPassword']);
        Route::post('/logout', [AuthController::class, 'userLogout'])->middleware('auth:sanctum');
    });
});
