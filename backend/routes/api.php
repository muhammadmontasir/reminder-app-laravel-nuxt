<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\ImportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{eventId}', [EventController::class, 'show']);
    Route::post('events', [EventController::class, 'store']);
    Route::put('events/{eventId}', [EventController::class, 'update']);
    Route::patch('events/{eventId}', [EventController::class, 'update']);
    Route::delete('events/{eventId}', [EventController::class, 'destroy']);
    
    Route::post('events/sync', [SyncController::class, 'sync'])
        ->middleware('throttle:60,1');
        
    Route::post('events/import', [ImportController::class, 'import'])
        ->middleware('throttle:30,1');
});