<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends Controller
{
    public function sendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.',
            ], 422);
        }

        $rateLimitKey = 'email_verification:' . $user->id;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 2)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many verification emails sent. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 600); // 10 minutes

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent successfully.',
        ]);
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.',
            ], 422);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
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

    public function checkVerificationStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'is_verified' => $user->hasVerifiedEmail(),
            'email_verified_at' => $user->email_verified_at,
        ]);
    }
}