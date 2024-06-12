<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendCodeRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $twilio;

    /**
     * AuthController constructor.
     *
     * @param TwilioService $twilio
     */
    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    /**
     * Handle user login.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
            'device_name' => 'required|string',
        ]);

        // Attempt to find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and the provided password matches the stored hash
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate and return an authentication token
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'error' => false
        ]);
    }

    /**
     * Handle user registration.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $data = $request->all();

        // Validate the incoming request data
        $validator = Validator::make($data, [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'device_name' => 'required|string',
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'error' => true
            ], 422);
        }

        // Create a new user with the provided data
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate and return an authentication token
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'error' => false
        ]);
    }

    public function sendCode(SendCodeRequest $request)
    {
        $code = rand(1000, 9999);
        VerificationCode::create([
            'phone_number' => $request->phone,
            'code' => $code,
        ]);
        try {
            return response()->json(['code' => $code]);
            //  $this->twilio->sendSms($request->phone_number, "Your verification code is: $code");
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $verificationCode = VerificationCode::where('phone_number', $request->phone_number)
            ->where('code', $request->code)
            ->first();

        if (!$verificationCode) {
            return response()->json(['error' => 'Invalid code'], 401);
        }

        // Optionally, delete the verification code after successful verification
        $verificationCode->delete();

        // Authenticate the user
//        $user = User::firstOrCreate(['phone' => $request->phone_number]);
//        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['error' => false, 'message' => 'Phone number is valid'], 200);

    }
}
