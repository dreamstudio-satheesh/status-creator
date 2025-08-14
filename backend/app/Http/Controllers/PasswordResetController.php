<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MSG91Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    private MSG91Service $msg91Service;

    public function __construct(MSG91Service $msg91Service)
    {
        $this->msg91Service = $msg91Service;
    }

    public function sendResetOtp(Request $request)
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
        $rateLimitKey = 'password_reset_otp:' . $mobile;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 2)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many password reset requests. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        $user = User::where('mobile', $mobile)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this mobile number',
            ], 404);
        }

        RateLimiter::hit($rateLimitKey, 600); // 10 minutes decay

        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $resetKey = 'password_reset:' . $mobile;
        
        Cache::put($resetKey, [
            'otp' => $otp,
            'user_id' => $user->id,
            'attempts' => 0,
            'created_at' => now(),
        ], 600); // 10 minutes

        $result = $this->msg91Service->sendOTP($mobile, $otp);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset OTP sent to your mobile number',
                'expires_in' => 600,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }

    public function verifyResetOtp(Request $request)
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
        $resetKey = 'password_reset:' . $mobile;
        $rateLimitKey = 'verify_reset_otp:' . $mobile;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many verification attempts. Please request a new OTP.',
            ], 429);
        }

        $resetData = Cache::get($resetKey);

        if (!$resetData) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired or not found. Please request a new password reset OTP.',
            ], 422);
        }

        if ($resetData['attempts'] >= 3) {
            Cache::forget($resetKey);
            return response()->json([
                'success' => false,
                'message' => 'Maximum OTP attempts exceeded. Please request a new password reset OTP.',
            ], 422);
        }

        RateLimiter::hit($rateLimitKey, 300);

        if ($resetData['otp'] !== $otp) {
            $resetData['attempts']++;
            Cache::put($resetKey, $resetData, 600);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. ' . (3 - $resetData['attempts']) . ' attempts remaining.',
            ], 422);
        }

        $resetToken = Str::random(60);
        $tokenKey = 'password_reset_token:' . $resetToken;
        
        Cache::put($tokenKey, [
            'user_id' => $resetData['user_id'],
            'mobile' => $mobile,
            'verified_at' => now(),
        ], 1800); // 30 minutes

        Cache::forget($resetKey);
        RateLimiter::clear($rateLimitKey);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully. You can now reset your password.',
            'reset_token' => $resetToken,
            'expires_in' => 1800,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $resetToken = $request->reset_token;
        $tokenKey = 'password_reset_token:' . $resetToken;
        $rateLimitKey = 'password_reset:' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many password reset attempts. Please try again later.',
            ], 429);
        }

        $tokenData = Cache::get($tokenKey);

        if (!$tokenData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token.',
            ], 422);
        }

        RateLimiter::hit($rateLimitKey, 3600); // 1 hour decay

        $user = User::find($tokenData['user_id']);

        if (!$user) {
            Cache::forget($tokenKey);
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $user->tokens()->delete();

        Cache::forget($tokenKey);
        RateLimiter::clear($rateLimitKey);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. Please login with your new password.',
        ]);
    }

    public function sendEmailResetLink(Request $request)
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

        $rateLimitKey = 'email_reset:' . $request->email;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 2)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many reset requests. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 600);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email address.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to send password reset link. Please check your email address.',
        ], 422);
    }
}