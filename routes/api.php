<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Driver\GpsController;
use Illuminate\Support\Facades\Route;

Route::post('/mobile/login', [MobileAuthController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('api.mobile.login');

Route::middleware(['auth:sanctum', 'role:driver', 'mobile-driver', 'abilities:driver:gps'])
    ->prefix('mobile')
    ->group(function () {
        Route::post('/logout', [MobileAuthController::class, 'logout'])
            ->name('api.mobile.logout');

        Route::post('/driver/gps/update', [GpsController::class, 'update'])
            ->name('api.mobile.driver.gps.update');
    });
