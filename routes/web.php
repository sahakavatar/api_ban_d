<?php

use App\Http\Controllers\Auth\GoogleController;
use GuzzleHttp\Client;
use Laravel\Socialite\Facades\Socialite;


use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test', function () {

    $client = new Client();
    $response = $client->post('https://oauth2.googleapis.com/token', [
        'form_params' => [
            'code' => '4/0ATx3LY6EnIkjNeBYSc6jTebpstzklYh4HbHPhYYcjKkY9EdbNerWUNm_I88sIIKkoAWZXg',
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect'),
            'grant_type' => 'authorization_code',
        ],
    ]);

    return json_decode($response->getBody(), true);
});


Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/google', [GoogleController::class, 'handleGoogleCallback']);

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
