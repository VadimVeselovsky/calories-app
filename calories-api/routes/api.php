<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthApi;
use App\Http\Controllers\MealsController;

Route::post('login', [AuthApi::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('can:meals,users')
        ->prefix('admin')
        ->group(function () {
            Route::apiResource('meals', MealsController::class);
        });

    Route::middleware('can:meals')
        ->prefix('app')
        ->group(function () {
            Route::apiResource('meals', MealsController::class);
        });
});
