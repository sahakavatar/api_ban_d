<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        Socialite::driver('google');
        try {
            $googleUser = Socialite::driver('google')->user();
            dd($googleUser->token);
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                Auth::login($user);
            } else {
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(uniqid()) // Generate a random password for this example
                ]);

                Auth::login($newUser);
            }

            return redirect()->intended('/home');
        } catch (Exception $e) {
            return redirect('auth/google');
        }
    }

    public function login(SocialLoginRequest $request)
    {
        try {
            $amazonUser = Socialite::driver('google')->userFromToken($request->access_token);
            $user = User::firstOrNew([
                'email' => $amazonUser->email]);
            $user->password = ($user->password) ? $user->password : Hash::make(Str::random(8));
            $user->save();
            $token = $user->createToken($request->device_name)->plainTextToken;
            return response()->json(['error' => false, 'token' => $token], 200);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }
}

