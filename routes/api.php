<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, ServiceController, ReservationController, AdminReservationController};
use App\Http\Middleware\{IsAdmin, EnsureReservationNotConfirmed, EnsureUserOwnsReservation};

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Service routes
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);

    // Reservation routes
    Route::prefix('reservations')->group(function () {
        Route::post('/', [ReservationController::class, 'store']);
        Route::get('/', [ReservationController::class, 'index']);

        // User-owned reservation routes
        Route::middleware([
            EnsureUserOwnsReservation::class,
            EnsureReservationNotConfirmed::class
        ])->put('/confirm/{id}', [ReservationController::class, 'confirm']);

        Route::middleware(EnsureUserOwnsReservation::class)->group(function () {
            Route::put('/{id}', [ReservationController::class, 'updateReservationTime']);
            Route::delete('/{id}', [ReservationController::class, 'cancel']);
        });
    });

    // Admin routes
    Route::middleware(IsAdmin::class)->group(function () {
        // Service management
        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);

        // Reservation management
        Route::prefix('reservations')->group(function () {
            Route::get('/all', [AdminReservationController::class, 'list']);
            Route::get('/{id}', [AdminReservationController::class, 'show']);
            Route::put('/status/{id}', [AdminReservationController::class, 'updateStatus']);
            Route::patch('/{id}/cancel', [AdminReservationController::class, 'cancel']);
            Route::get('/export/csv', [AdminReservationController::class, 'export']);
        });
    });
});