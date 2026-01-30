<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserAchievementController;
use App\Http\Controllers\Api\Admin\AdminAchievementController;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\PurchaseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/**
 * Public routes
 */
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Admin authentication
    Route::post('/admin/login', [AdminAuthController::class, 'login']);
});

/**
 * Protected customer routes
 */
Route::prefix('v1')->middleware(['auth:jwt'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'user']);

    // Loyalty dashboard
    Route::get('/loyalty/dashboard', [UserAchievementController::class, 'dashboard']);

    // User achievements
    Route::prefix('users/{user}')->group(function () {
        Route::get('/achievements', [UserAchievementController::class, 'index']);
        Route::get('/achievements/list', [UserAchievementController::class, 'achievements']);
        Route::get('/badges', [UserAchievementController::class, 'badges']);
        Route::get('/cashback', [UserAchievementController::class, 'cashback']);
    });

    // Purchases (for testing/demo)
    Route::prefix('purchases')->group(function () {
        Route::get('/', [PurchaseController::class, 'index']);
        Route::post('/', [PurchaseController::class, 'store']);
        Route::get('/statistics', [PurchaseController::class, 'statistics']);
        Route::get('/{purchase}', [PurchaseController::class, 'show']);
        Route::post('/{purchase}/complete', [PurchaseController::class, 'complete']);
    });
});

/**
 * Protected admin routes
 */
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin logout
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/user', [AdminAuthController::class, 'user']);
    Route::get('/users', [AdminAuthController::class, 'users']);

    // Users and achievements
    Route::get('/users/achievements', [AdminAchievementController::class, 'index']);
    Route::get('/users/{user}/loyalty', [AdminAchievementController::class, 'show']);

    // Statistics
    Route::get('/loyalty/stats', [AdminAchievementController::class, 'statistics']);
});

