<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     tags={"Authentication"},
     *     summary="Register new user with email and password",
     *     description="Creates a new user account and automatically logs them in",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="mobile", type="string", example="+919876543210", description="Optional mobile number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration successful. You are now logged in."),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="1|abcd1234...")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'nullable|string|regex:/^[+]?[0-9]{10,15}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $rateLimitKey = 'register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many registration attempts. Please try again later.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 3600); // 1 hour decay

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->mobile,
            'subscription_type' => 'free',
            'daily_ai_quota' => 10,
            'daily_ai_used' => 0,
            'email_verified_at' => now(), // Auto-verify email on registration
        ]);

        // Create authentication token for immediate login
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. You are now logged in.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'avatar' => $user->avatar,
                'subscription_type' => $user->subscription_type,
                'subscription_expires_at' => $user->subscription_expires_at,
                'daily_ai_quota' => $user->daily_ai_quota,
                'daily_ai_used' => $user->daily_ai_used,
                'is_premium' => $user->isPremium(),
                'email_verified_at' => $user->email_verified_at,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Authentication"},
     *     summary="Login with email and password",
     *     description="Authenticates user with email and password"
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $rateLimitKey = 'login:' . $email;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        if (!Auth::attempt(['email' => $email, 'password' => $request->password])) {
            RateLimiter::hit($rateLimitKey, 900); // 15 minutes decay
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 422);
        }

        RateLimiter::clear($rateLimitKey);
        $user = Auth::user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'avatar' => $user->avatar,
                'subscription_type' => $user->subscription_type,
                'subscription_expires_at' => $user->subscription_expires_at,
                'daily_ai_quota' => $user->daily_ai_quota,
                'daily_ai_used' => $user->daily_ai_used,
                'is_premium' => $user->isPremium(),
                'email_verified_at' => $user->email_verified_at,
            ],
            'token' => $token,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/forgot-password",
     *     tags={"Authentication"},
     *     summary="Send password reset link",
     *     description="Sends password reset link to user's email"
     * )
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email format',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $rateLimitKey = 'forgot_password:' . $email;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many password reset requests. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 3600); // 1 hour decay

        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to send password reset link. Please check your email address.',
        ], 422);
    }

    public function googleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('email', $googleUser->email)
                ->orWhere('google_id', $googleUser->id)
                ->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }
            } else {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'email_verified_at' => now(),
                    'subscription_type' => 'free',
                    'daily_ai_quota' => 10,
                    'daily_ai_used' => 0,
                ]);
            }

            $token = $user->createToken('google_auth')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Google authentication successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'avatar' => $user->avatar,
                    'subscription_type' => $user->subscription_type,
                    'subscription_expires_at' => $user->subscription_expires_at,
                    'daily_ai_quota' => $user->daily_ai_quota,
                    'daily_ai_used' => $user->daily_ai_used,
                    'is_premium' => $user->isPremium(),
                ],
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function googleAuthenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'mobile' => 'sometimes|string|regex:/^[+]?[0-9]{10,15}$/',
            'avatar' => 'sometimes|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // In a real implementation, you would verify the Firebase ID token here
            // For now, we'll trust the data from the client
            $email = $request->email;
            $name = $request->name;
            $mobile = $request->mobile;
            $avatar = $request->avatar;

            $user = User::where('email', $email)->first();

            if ($user) {
                // Update user info if changed
                $updateData = [
                    'name' => $name,
                    'avatar' => $avatar,
                    'email_verified_at' => now(),
                ];
                
                // Only update mobile if provided
                if ($mobile) {
                    $updateData['mobile'] = $mobile;
                }
                
                $user->update($updateData);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'mobile' => $mobile,
                    'avatar' => $avatar,
                    'email_verified_at' => now(),
                    'subscription_type' => 'free',
                    'daily_ai_quota' => 10,
                    'daily_ai_used' => 0,
                ]);
            }

            $token = $user->createToken('google_auth')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Google authentication successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'avatar' => $user->avatar,
                    'subscription_type' => $user->subscription_type,
                    'subscription_expires_at' => $user->subscription_expires_at,
                    'daily_ai_quota' => $user->daily_ai_quota,
                    'daily_ai_used' => $user->daily_ai_used,
                    'is_premium' => $user->isPremium(),
                ],
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'avatar' => $user->avatar,
                'subscription_type' => $user->subscription_type,
                'subscription_expires_at' => $user->subscription_expires_at,
                'daily_ai_quota' => $user->daily_ai_quota,
                'daily_ai_used' => $user->daily_ai_used,
                'is_premium' => $user->isPremium(),
                'created_at' => $user->created_at,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'avatar' => 'sometimes|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'avatar']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'avatar' => $user->avatar,
                'subscription_type' => $user->subscription_type,
                'subscription_expires_at' => $user->subscription_expires_at,
                'daily_ai_quota' => $user->daily_ai_quota,
                'daily_ai_used' => $user->daily_ai_used,
                'is_premium' => $user->isPremium(),
            ],
        ]);
    }

    public function resetQuota(Request $request)
    {
        $user = $request->user();
        
        if ($user->last_quota_reset !== today()) {
            $user->update([
                'daily_ai_used' => 0,
                'last_quota_reset' => today(),
            ]);
        }

        return response()->json([
            'success' => true,
            'daily_ai_quota' => $user->daily_ai_quota,
            'daily_ai_used' => $user->daily_ai_used,
            'remaining_quota' => $user->daily_ai_quota - $user->daily_ai_used,
        ]);
    }
}