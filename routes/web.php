<?php

use App\Http\Controllers\Auth\GoogleController;
use Laravel\Socialite\Facades\Socialite;


use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('/auth/amazon/redirect', function () {
    return Socialite::driver('amazon')->redirect();
});

Route::get('/auth/amazon/callback', function () {
    $user = Socialite::driver('amazon')->user();

     dd($user->token);
});


Route::get('/auth/google/test-token/{token}', function ($token) {
    $user = $user = Socialite::driver('google')->userFromToken($token);

     dd($user);
});
Route::get('/auth/amazon/test-token/{token}', function ($token) {
    $user = $user = Socialite::driver('amazon')->userFromToken($token);

     dd($user);
});
