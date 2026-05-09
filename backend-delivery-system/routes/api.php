<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeliveryOrderController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Auth Routes (no token required)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes — JWT (auth:api)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    // ── Auth ─────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::get('me',       [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout',  [AuthController::class, 'logout']);
    });

    // ── Delivery Orders ──────────────────────────────────────────────────
    Route::prefix('orders')->group(function () {

        Route::middleware('role:admin|driver')->group(function () {
            Route::get('/',                [DeliveryOrderController::class, 'index']);
            Route::get('/{deliveryOrder}', [DeliveryOrderController::class, 'show']);
            Route::post('/{deliveryOrder}/status', [DeliveryOrderController::class, 'updateStatus']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::post('/',                       [DeliveryOrderController::class, 'store']);
            Route::put('/{deliveryOrder}',         [DeliveryOrderController::class, 'update']);
            Route::post('/{deliveryOrder}/assign', [DeliveryOrderController::class, 'assignDriver']);
            Route::post('/{deliveryOrder}/cancel', [DeliveryOrderController::class, 'cancel']);
        });
    });

    // ── Drivers ──────────────────────────────────────────────────────────
    Route::prefix('drivers')->group(function () {

        // Must be BEFORE /{driver} wildcard to avoid route conflict
        Route::middleware('role:admin|driver')->group(function () {
            Route::post('/location', [DriverController::class, 'updateLocation']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('/',                          [DriverController::class, 'index']);
            Route::post('/',                         [DriverController::class, 'store']);
            Route::get('/live-locations',            [DriverController::class, 'liveLocations']);
            Route::get('/{driver}',                  [DriverController::class, 'show']);
            Route::patch('/{driver}/status',         [DriverController::class, 'updateStatus']);
            Route::get('/{driver}/location-history', [DriverController::class, 'locationHistory']);
        });
    });

    // ── Routes / Navigation ──────────────────────────────────────────────
    Route::prefix('routes')->group(function () {

        Route::middleware('role:admin|driver')->group(function () {
            Route::get('/{deliveryOrder}',     [RouteController::class, 'show']);
            Route::get('/{deliveryOrder}/eta', [RouteController::class, 'eta']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::post('/{deliveryOrder}/optimize', [RouteController::class, 'reOptimize']);
        });
    });

    // ── Reports & Analytics (Admin only) ─────────────────────────────────
    Route::prefix('reports')->middleware('role:admin')->group(function () {
        Route::get('/dashboard',                    [ReportController::class, 'dashboard']);
        Route::get('/orders',                       [ReportController::class, 'ordersReport']);
        Route::get('/drivers/{driver}/performance', [ReportController::class, 'driverPerformance']);
        Route::post('/generate-daily',              [ReportController::class, 'generateDailyPerformance']);
    });
});
