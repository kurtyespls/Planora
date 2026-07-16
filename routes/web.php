<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanoraController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

// I-load ang main page
Route::get('/', [PlanoraController::class, 'index']);
Route::get('/planora', [PlanoraController::class, 'index']);

// Auth Routes
Route::middleware(['guest', 'prevent.back'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout']);

// Profile Routes
Route::get('/profile/{id}', [AuthController::class, 'showProfile'])->middleware('auth');
Route::put('/profile/{id}', [AuthController::class, 'updateProfile'])->middleware('auth');

// API Routes with rate limiting
Route::get('/api/hotels', [PlanoraController::class, 'getHotels'])->middleware('throttle.api:60,1');

Route::post('/generate-plan', [PlanoraController::class, 'generatePlan'])->middleware('throttle.api:20,1');
Route::get('/api/nearby-places', [PlanoraController::class, 'getNearbyPlaces'])->middleware('throttle.api:30,1');
Route::get('/api/weather', [PlanoraController::class, 'getWeather'])->middleware('throttle.api:60,1');

// API routes for check-in/check-out/tourist spots (stub implementations)
Route::middleware('auth')->group(function () {
    Route::post('/api/visit-log/checkin', function (\Illuminate\Http\Request $request) {
        return response()->json(['success' => true, 'message' => 'Check-in recorded']);
    });
    Route::post('/api/visit-log/checkout', function (\Illuminate\Http\Request $request) {
        return response()->json(['success' => true, 'message' => 'Check-out recorded', 'duration_minutes' => 45]);
    });
    Route::get('/api/tourist-spots', function (\Illuminate\Http\Request $request) {
        return response()->json([]);
    });
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/hotels', [AdminController::class, 'index']);
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroyUser']);
    Route::post('/admin/hotels', [AdminController::class, 'store']);
    Route::delete('/admin/hotels/{id}', [AdminController::class, 'destroy']);
    Route::put('/admin/hotels/{id}', [AdminController::class, 'update']);
});