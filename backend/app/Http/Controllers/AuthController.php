<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MSG91Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private MSG91Service $msg91Service;

    public function __construct(MSG91Service $msg91Service)
    {
        $this->msg91Service = $msg91Service;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/send-otp",
     *     tags={"Authentication"},
     *     summary="Send OTP to mobile number",
     *     description="Sends a 6-digit OTP to the provided mobile number for authentication",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="mobile", type="string", example="+919876543210", description="Mobile number with country code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP sent successfully to your mobile number"),
     *             @OA\Property(property="expires_in", type="integer", example=300)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid mobile number format"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Too many OTP requests. Please try again in 5 minutes.")
     *         )
     *     )
     * )
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|regex:/^[+]?[0-9]{10,15}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mobile number format',
                'errors' => $validator->errors(),
            ], 422);
        }

        $mobile = $request->mobile;
        $rateLimitKey = 'send_otp:' . $mobile;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many OTP requests. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 300); // 5 minutes decay

        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $otpKey = 'otp:' . $mobile;
        
        Cache::put($otpKey, [
            'otp' => $otp,
            'attempts' => 0,
            'created_at' => now(),
        ], 300); // 5 minutes

        $result = $this->msg91Service->sendOTP($mobile, $otp);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to your mobile number',
                'expires_in' => 300,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|regex:/^[+]?[0-9]{10,15}$/',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data',
                'errors' => $validator->errors(),
            ], 422);
        }

        $mobile = $request->mobile;
        $otp = $request->otp;
        $otpKey = 'otp:' . $mobile;
        $rateLimitKey = 'verify_otp:' . $mobile;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many verification attempts. Please request a new OTP.',
            ], 429);
        }

        $otpData = Cache::get($otpKey);

        if (!$otpData) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired or not found. Please request a new OTP.',
            ], 422);
        }

        if ($otpData['attempts'] >= 3) {
            Cache::forget($otpKey);
            return response()->json([
                'success' => false,
                'message' => 'Maximum OTP attempts exceeded. Please request a new OTP.',
            ], 422);
        }

        RateLimiter::hit($rateLimitKey, 300);

        if ($otpData['otp'] !== $otp) {
            $otpData['attempts']++;
            Cache::put($otpKey, $otpData, 300);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. ' . (3 - $otpData['attempts']) . ' attempts remaining.',
            ], 422);
        }

        Cache::forget($otpKey);
        RateLimiter::clear($rateLimitKey);

        $user = User::where('mobile', $mobile)->first();

        if (!$user) {
            $user = User::create([
                'name' => 'User',
                'mobile' => $mobile,
                'subscription_type' => 'free',
                'daily_ai_quota' => 10,
                'daily_ai_used' => 0,
            ]);
        }

        $token = $user->createToken('mobile_auth')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'subscription_type' => $user->subscription_type,
                'subscription_expires_at' => $user->subscription_expires_at,
                'daily_ai_quota' => $user->daily_ai_quota,
                'daily_ai_used' => $user->daily_ai_used,
                'is_premium' => $user->isPremium(),
            ],
            'token' => $token,
        ]);
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|regex:/^[+]?[0-9]{10,15}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mobile number format',
                'errors' => $validator->errors(),
            ], 422);
        }

        $mobile = $request->mobile;
        $rateLimitKey = 'resend_otp:' . $mobile;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 2)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many resend requests. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 600); // 10 minutes decay

        $otpKey = 'otp:' . $mobile;
        
        if (!Cache::has($otpKey)) {
            return response()->json([
                'success' => false,
                'message' => 'No active OTP session found. Please request a new OTP.',
            ], 422);
        }

        $otp = sprintf('%06d', mt_rand(100000, 999999));
        
        Cache::put($otpKey, [
            'otp' => $otp,
            'attempts' => 0,
            'created_at' => now(),
        ], 300);

        $result = $this->msg91Service->sendOTP($mobile, $otp);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'OTP resent successfully',
                'expires_in' => 300,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
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