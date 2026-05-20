<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => Inertia::render('Home', [
    'message' => 'Inertia funcionando!',
]));

Route::resource('/tasks', TaskController::class)->only(['index', 'store', 'update', 'destroy']);
