<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
    public function redirectToGoogle():Response|RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback(Request $request)
    {
        Socialite::driver('google');
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                $token = $user->createToken('app')->plainTextToken;
            } else {
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(uniqid()) // Generate a random password for this example
                ]);

                $token = $newUser->createToken('app')->plainTextToken;
            }

            return redirect()->to('http://localhost:8081/auth/login?token=' . $token);
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

