<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanoraController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\PlanController;
use App\Http\Controllers\VisitLogController;

// Role-aware landing page sa bare domain (admin -> control center, bisita ->
// marketing page), habang ang /planora ay laging public app para gumana ang
// "View Live App" / "Visit App" links ng admin panel kahit admin ang naka-login.
Route::get('/', [PlanoraController::class, 'home']);
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
Route::get('/api/nearby-places', [PlanoraController::class, 'getNearbyPlaces'])->middleware('throttle.api:30,1');
Route::get('/api/weather', [PlanoraController::class, 'getWeather'])->middleware('throttle.api:60,1');
Route::get('/api/tourist-spots', [PlanoraController::class, 'getTouristSpots'])->middleware('throttle.api:60,1');

// Authenticated planning, visit-log at plan-view routes.
// /generate-plan is behind auth because it persists a plan owned by the current
// user and spends an external AI call.
Route::middleware('auth')->group(function () {
    Route::post('/generate-plan', [PlanoraController::class, 'generatePlan'])->middleware('throttle.api:20,1');

    Route::post('/api/visit-log/checkin', [VisitLogController::class, 'checkIn'])->middleware('throttle.api:60,1');
    Route::post('/api/visit-log/checkout', [VisitLogController::class, 'checkOut'])->middleware('throttle.api:60,1');

    // Plans routes — mag-view, mag-rename, i-regenerate at i-delete ng mga
    // na-generate na itinerary ng user
    Route::get('/plans/{plan}', [PlanController::class, 'show']);
    Route::patch('/plans/{plan}', [PlanController::class, 'rename']);
    Route::post('/plans/{plan}/regenerate', [PlanController::class, 'regenerate']);
    Route::delete('/plans/{plan}', [PlanController::class, 'destroy']);
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/hotels', [AdminController::class, 'index']);
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::get('/admin/users/{id}', [AdminController::class, 'showUser']);
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroyUser']);
    Route::post('/admin/hotels', [AdminController::class, 'store']);
    Route::delete('/admin/hotels/{id}', [AdminController::class, 'destroy']);
    Route::put('/admin/hotels/{id}', [AdminController::class, 'update']);
});