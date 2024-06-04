<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Define the API routes under the 'v1' prefix
Route::group(['prefix' => 'v1'], function () {

    // Route to send verification code
    Route::post('/send-code', [AuthController::class, 'sendCode']);

    // Route to verify the sent code
    Route::post('/verify-code', [AuthController::class, 'verifyCode']);

    // Route to get the authenticated user details
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Route to register a new user
    Route::post('/register', [AuthController::class, 'register']);

    // Route to login and get an authentication token
    Route::post('/sanctum/token', [AuthController::class, 'login']);
});
