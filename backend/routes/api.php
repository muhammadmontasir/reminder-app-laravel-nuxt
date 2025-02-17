<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\AuthController;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        
        Route::apiResource('events', EventController::class);
        
        Route::prefix('events')->group(function () {
            Route::post('/sync', [SyncController::class, 'sync'])
                ->middleware('throttle:60,1');
                
            Route::post('/import', [ImportController::class, 'import'])
                ->middleware('throttle:30,1');
        });
    });
});