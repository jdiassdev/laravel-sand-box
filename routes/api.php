<?php

use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('orders')->group(function () {
    Route::get('/',                      [OrderController::class, 'index']);
    Route::get('/queue-status',          [OrderController::class, 'queueStatus']);
    Route::get('/{order}',               [OrderController::class, 'show']);
    Route::post('/simple',               [OrderController::class, 'store']);
    Route::post('/with-chain',           [OrderController::class, 'storeWithChain']);
    Route::post('/with-delay',           [OrderController::class, 'storeWithDelay']);
    Route::post('/priority-queues',      [OrderController::class, 'storeInPriorityQueue']);
});
