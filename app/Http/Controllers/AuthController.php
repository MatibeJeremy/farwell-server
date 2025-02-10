<?php

namespace App\Http\Controllers;

use App\Mail\RegisterMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


/**
 * @OA\Info(
 *     title="Farwell server apis",
 *     version="1.0.0",
 *     description="Auth, User, and Employee APIs",
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully. Please use the activation code to activate your account."),
     *             @OA\Property(property="activation_token", type="string", example="random_activation_token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Generate an activation token
        $activationToken = Str::random(60);

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'activation_token' => $activationToken
        ]);

        // Prepare email details
        $details = [
            'title' => 'Activation Code',
            'body' => $activationToken
        ];

        // Send activation email
        Mail::to($user->email)->send(new RegisterMail($details));

        // Return success response
        return response()->json([
            'message' => 'User registered successfully. Please use the activation code to activate your account.',
            'activation_token' => $activationToken
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login a user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="access_token"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Account not activated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Please activate your account.")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            if ($user->email_verified_at == null) {
                return response()->json(['message' => 'Please activate your account.'], 403);
            }
            $token = $user->createToken('LaravelPassportAuth')->accessToken;
            return response()->json(['token' => $token, 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    /**
     * @OA\Get(
     *     path="/activate/{token}",
     *     summary="Activate a user account",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account activated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Account activated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid activation token",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This activation token is invalid.")
     *         )
     *     )
     * )
     */
    public function activate($token)
    {
        // Find the user by activation token
        $user = User::where('activation_token', $token)->first();

        // Return error if the token is invalid
        if (!$user) {
            return response()->json(['message' => 'This activation token is invalid.'], 404);
        }

        // Activate the user account
        $user->email_verified_at = now();
        $user->activation_token = '';
        $user->save();

        // Return success response
        return response()->json(['message' => 'Account activated successfully.'], 200);
    }
}
