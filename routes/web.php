<?php

use App\Http\Controllers\Auth\AmazonController;
use App\Http\Controllers\Auth\GoogleController;
use GuzzleHttp\Client;
use Laravel\Socialite\Facades\Socialite;


use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/google', [GoogleController::class, 'handleGoogleCallback']);

Route::get('/auth/amazon/redirect', function () {
    return Socialite::driver('amazon')->redirect();
});

Route::get('/auth/amazon/callback',[AmazonController::class,'handleProviderCallback']);


Route::get('/auth/google/test-token/{token}', function ($token) {
    $user = $user = Socialite::driver('google')->userFromToken($token);

     dd($user);
});
Route::get('/auth/amazon/test-token/{token}', function ($token) {
    $user = $user = Socialite::driver('amazon')->userFromToken($token);

     dd($user);
});
