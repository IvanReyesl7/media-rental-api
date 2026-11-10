<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MediaController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', fn () => ['message' => 'Hello from Laravel']);

Route::get('/media/{media}/availability', [MediaController::class, 'availability']);
Route::apiResource('media', MediaController::class)->parameters(['media' => 'media']);

Route::apiResource('customers', CustomerController::class)->only(['index', 'store', 'show']);

Route::apiResource('bookings', BookingController::class)->only(['index', 'store', 'show']);
Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);