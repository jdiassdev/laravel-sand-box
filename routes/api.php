<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('oop')->group(function () {
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::post('/refund', [OrderController::class, 'refund']);
});
