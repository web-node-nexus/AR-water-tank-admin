<?php

use App\Http\Controllers\Api\Provider\AttendanceController;
use App\Http\Controllers\Api\Provider\AuthController;
use App\Http\Controllers\Api\Provider\CallController;
use App\Http\Controllers\Api\Provider\DashboardController;
use App\Http\Controllers\Api\Provider\EarningsController;
use App\Http\Controllers\Api\Provider\FeedbackController;
use App\Http\Controllers\Api\Provider\JobController;
use App\Http\Controllers\Api\Provider\LeaveController;
use App\Http\Controllers\Api\Provider\ProfileController;
use App\Http\Controllers\Api\Provider\SupportController;
use Illuminate\Support\Facades\Route;

Route::prefix('provider')->group(function () {
    // Public auth routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

    // Exotel webhook (no auth)
    Route::post('/calls/callback', [CallController::class, 'callback']);

    // Protected provider routes
    Route::middleware(['auth:sanctum', 'provider'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/jobs', [JobController::class, 'index']);
        Route::get('/jobs/{booking}', [JobController::class, 'show']);
        Route::post('/jobs/{booking}/accept', [JobController::class, 'accept']);
        Route::post('/jobs/{booking}/reject', [JobController::class, 'reject']);
        Route::post('/jobs/{booking}/start', [JobController::class, 'start']);
        Route::post('/jobs/{booking}/complete', [JobController::class, 'complete']);
        Route::post('/jobs/{booking}/photos', [JobController::class, 'uploadPhoto']);

        Route::post('/jobs/{booking}/call', [CallController::class, 'callCustomer']);
        Route::get('/calls/{callLog}', [CallController::class, 'status']);

        Route::get('/profile', [ProfileController::class, 'show']);
        Route::match(['put', 'post'], '/profile', [ProfileController::class, 'update']);
        Route::post('/profile/fcm-token', [ProfileController::class, 'updateFcmToken']);

        Route::get('/earnings', [EarningsController::class, 'index']);

        Route::get('/feedback', [FeedbackController::class, 'index']);

        Route::get('/leaves', [LeaveController::class, 'index']);
        Route::post('/leaves', [LeaveController::class, 'store']);
        Route::post('/availability', [LeaveController::class, 'toggleAvailability']);

        Route::get('/support', [SupportController::class, 'index']);
        Route::post('/support', [SupportController::class, 'store']);

        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/attendance/today', [AttendanceController::class, 'today']);
        Route::get('/attendance/history', [AttendanceController::class, 'history']);
    });
});
