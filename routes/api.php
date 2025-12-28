<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/currencies', [CurrencyController::class, 'getRates']);
    Route::post('/convert', [CurrencyController::class, 'convert']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
