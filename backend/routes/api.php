<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

Route::prefix('v1')->group(function () {
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{eventId}', [EventController::class, 'show']);
    Route::post('events', [EventController::class, 'store']);
    Route::put('events/{eventId}', [EventController::class, 'update']);
    Route::delete('events/{eventId}', [EventController::class, 'destroy']);
    
    Route::post('events/sync', [SyncController::class, 'sync'])
        ->middleware('throttle:60,1');
        
    Route::post('events/import', [ImportController::class, 'import'])
        ->middleware('throttle:30,1');
});