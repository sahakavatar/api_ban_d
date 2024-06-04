<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use function Laravel\Prompts\error;

class PasswordResetController extends Controller
{
    // Send reset code
    public function sendResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>true,'messages'=>$validator->errors()], 422);
        }

        $code = rand(1000, 9999);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $code, 'created_at' => Carbon::now()]
        );

        // Send email with the reset code
//        Mail::raw("Your password reset code is $code", function($message) use ($request) {
//            $message->to($request->email)
//                ->subject('Password Reset Code');
//        });

        return response()->json(['error' => false, 'message' => 'Reset code sent to your email.', 'code' => $code, 'email' => $request->email], 200);
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|numeric|digits:4',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>true,'messages'=>$validator->errors()], 422);
        }

        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$passwordReset) {
            return response()->json(['error'=>true,'message' => 'Invalid reset code.'], 400);
        }

        // Check if the reset code is expired (valid for 1 hour)
        if (Carbon::parse($passwordReset->created_at)->addHour()->isPast()) {
            return response()->json(['error'=>true,'message' => 'Reset code has expired.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the reset token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['error' => false, 'message' => 'Password reset successfully.'], 200);
    }
}
