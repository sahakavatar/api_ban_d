<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AmazonController extends Controller
{
    /**
     * Redirect the user to the Amazon authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider():RedirectResponse
    {
        return Socialite::driver('amazon')->redirect();
    }

    /**
     * Obtain the user information from Amazon.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(): RedirectResponse
    {
        try {
            $amazonUser = Socialite::driver('amazon')->user();
            $user = User::where('email', $amazonUser->email)->first();

            if ($user) {
                $token = $user->createToken('app')->plainTextToken;
            } else {
                $newUser = User::create([
                    'name' => $amazonUser->name,
                    'email' => $amazonUser->email,
                    'amazon_id' => $amazonUser->id,
                    'password' => Hash::make(uniqid()) // Generate a random password for this example
                ]);

                Auth::login($newUser);
            }

            return redirect()->to(env('APP_FRONT_URL') . '/auth?token=' . $token);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect('auth/google');
        }
    }

    public function login(SocialLoginRequest $request)
    {
        try {
            $amazonUser = Socialite::driver('amazon')->userFromToken($request->access_token);
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

