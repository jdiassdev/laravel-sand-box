<?php

use App\Http\Controllers\OctaneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('octane-demo')->group(function () {
    // Conceito 1: State Leak
    Route::post('/cart/add',    [OctaneController::class, 'cartAdd']);
    Route::get('/cart',         [OctaneController::class, 'cartView']);
    Route::delete('/cart',      [OctaneController::class, 'cartClear']);

    // Conceito 2: Singleton vs Bind
    Route::get('/notify/singleton', [OctaneController::class, 'notifySingleton']);
    Route::get('/notify/bind',      [OctaneController::class, 'notifyBind']);
    Route::delete('/notify/reset',  [OctaneController::class, 'notifyReset']);

    // Conceito 3: Sequential vs Concurrent
    Route::get('/dashboard/sequential', [OctaneController::class, 'dashboardSequential']);
    Route::get('/dashboard/concurrent', [OctaneController::class, 'dashboardConcurrent']);

    // Conceito 4: Ticks
    Route::get('/ticks/status', [OctaneController::class, 'ticksStatus']);
});
