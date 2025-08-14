<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans = [
            'basic' => [
                'name' => 'Basic',
                'price' => 0,
                'currency' => 'INR',
                'duration' => 'monthly',
                'features' => [
                    '5 AI generations per day',
                    'Basic templates',
                    'Standard themes',
                    'Community support'
                ],
                'ai_quota' => 5,
                'is_premium' => false
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 299,
                'currency' => 'INR',
                'duration' => 'monthly',
                'features' => [
                    'Unlimited AI generations',
                    'All premium templates',
                    'All themes',
                    'Priority support',
                    'HD downloads',
                    'Custom fonts'
                ],
                'ai_quota' => -1, // Unlimited
                'is_premium' => true
            ],
            'yearly' => [
                'name' => 'Premium Yearly',
                'price' => 2999,
                'currency' => 'INR',
                'duration' => 'yearly',
                'features' => [
                    'All Premium features',
                    '2 months free',
                    'Priority customer support',
                    'Beta feature access'
                ],
                'ai_quota' => -1, // Unlimited
                'is_premium' => true,
                'discount_percentage' => 17
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $plans,
            'message' => 'Subscription plans retrieved successfully'
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:premium,yearly',
            'payment_method' => 'required|in:razorpay,upi,card',
        ]);

        $user = Auth::user();

        // Check if user already has active subscription
        if ($user->subscription_type === 'premium' && $user->subscription_expires_at && $user->subscription_expires_at->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active premium subscription'
            ], 400);
        }

        $plans = [
            'premium' => ['price' => 299, 'duration' => 30],
            'yearly' => ['price' => 2999, 'duration' => 365],
        ];

        $selectedPlan = $plans[$request->plan];

        // Create subscription record
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => $request->plan,
            'amount' => $selectedPlan['price'],
            'currency' => 'INR',
            'duration_days' => $selectedPlan['duration'],
            'payment_method' => $request->payment_method,
            'status' => 'pending',
            'payment_data' => $request->payment_data ?? [],
        ]);

        // Here you would integrate with actual payment gateway
        // For now, we'll return the subscription details
        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => $subscription,
                'payment_url' => "https://payment-gateway.com/pay/{$subscription->id}",
                'order_id' => 'order_' . $subscription->id,
            ],
            'message' => 'Subscription created. Please complete the payment.'
        ], 201);
    }

    public function cancel(Request $request)
    {
        $user = Auth::user();

        if ($user->subscription_type !== 'premium' || !$user->subscription_expires_at || $user->subscription_expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found'
            ], 400);
        }

        // Find active subscription
        $subscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found'
            ], 400);
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Note: We don't immediately remove premium status
        // Let it expire naturally based on premium_until date
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully. Premium benefits will remain active until the end of current billing period.',
            'data' => [
                'expires_at' => $user->subscription_expires_at,
            ]
        ]);
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        $subscriptions = Subscription::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $subscriptions,
            'message' => 'Subscription history retrieved successfully'
        ]);
    }
}