<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\EnsureReservationNotConfirmed;
use App\Http\Middleware\EnsureUserOwnsReservation;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);

    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations', [ReservationController::class, 'index']);
    
    Route::middleware([EnsureUserOwnsReservation::class])->group(function () {
        Route::middleware([EnsureReservationNotConfirmed::class])->group(function () {
            Route::put('/reservations/confirm/{id}', [ReservationController::class, 'confirm']);
        });
        Route::put('/reservations/{id}', [ReservationController::class, 'updateReservationTime']);
        Route::delete('/reservations/{id}', [ReservationController::class, 'cancel']);

    });

    
    Route::middleware([IsAdmin::class])->group(function () {
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{id}', [ServiceController::class, 'update']);
        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

        Route::get('/reservations/all', [AdminReservationController::class, 'list']);
        Route::get('/reservations/{id}', [AdminReservationController::class, 'show']);
        Route::put('/reservations/status/{id}', [AdminReservationController::class, 'updateStatus']);
        Route::patch('/reservations/{id}/cancel', [AdminReservationController::class, 'cancel']);
        Route::get('/reservations/export/csv', [AdminReservationController::class, 'export']); 
    });
});
