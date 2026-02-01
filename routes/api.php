<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
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

    Route::prefix('users/profile')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [UserProfileController::class, 'show']);
        Route::put('/', [UserProfileController::class, 'update']);
        Route::patch('/', [UserProfileController::class, 'update']);
        Route::put('/change-password', [UserProfileController::class, 'changePassword']);
        Route::delete('/', [UserProfileController::class, 'destroy']);
    });

    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/users', [UserController::class, 'getUsers']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/active', [UserController::class, 'getActiveUsers']);
        Route::get('/users/deleted', [UserController::class, 'getDeletedUsers']);
        Route::get('/users/admins', [UserController::class, 'getAdmins']);
        Route::get('/users/instructors', [UserController::class, 'getInstructors']);
        Route::get('/users/learners', [UserController::class, 'getLearners']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::post('/users/{id}/restore', [UserController::class, 'restore']);
        Route::delete('/users/{id}/force', [UserController::class, 'forceDestroy']);
    });

    Route::apiResource('categories', CourseCategoryController::class);
    Route::put('/categories/{category}/toggle-active', [CourseCategoryController::class, 'toggleActive']);
    
});
