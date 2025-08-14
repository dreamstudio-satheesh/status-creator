<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebhookController extends Controller
{
    public function razorpay(Request $request)
    {
        Log::info('Razorpay webhook received', $request->all());

        $event = $request->input('event');
        $payload = $request->input('payload');

        switch ($event) {
            case 'payment.captured':
                $this->handlePaymentCaptured($payload);
                break;
            case 'payment.failed':
                $this->handlePaymentFailed($payload);
                break;
            case 'subscription.cancelled':
                $this->handleSubscriptionCancelled($payload);
                break;
            default:
                Log::info('Unhandled Razorpay webhook event: ' . $event);
        }

        return response()->json(['status' => 'success']);
    }

    public function firebase(Request $request)
    {
        Log::info('Firebase webhook received', $request->all());

        $event = $request->input('event');
        $data = $request->input('data');

        switch ($event) {
            case 'user.created':
                $this->handleUserCreated($data);
                break;
            case 'user.deleted':
                $this->handleUserDeleted($data);
                break;
            default:
                Log::info('Unhandled Firebase webhook event: ' . $event);
        }

        return response()->json(['status' => 'success']);
    }

    private function handlePaymentCaptured($payload)
    {
        $paymentId = $payload['payment']['entity']['id'];
        $orderId = $payload['payment']['entity']['order_id'];
        $amount = $payload['payment']['entity']['amount'] / 100; // Convert from paise to rupees

        // Find subscription by order ID or payment metadata
        $subscription = Subscription::where('payment_data->order_id', $orderId)
            ->orWhere('payment_data->payment_id', $paymentId)
            ->first();

        if (!$subscription) {
            Log::error('Subscription not found for payment: ' . $paymentId);
            return;
        }

        // Update subscription status
        $subscription->update([
            'status' => 'active',
            'payment_id' => $paymentId,
            'paid_at' => now(),
            'payment_data' => array_merge($subscription->payment_data ?? [], [
                'razorpay_payment_id' => $paymentId,
                'razorpay_order_id' => $orderId,
            ])
        ]);

        // Update user premium status
        $user = $subscription->user;
        $subscriptionExpiresAt = $user->subscription_expires_at && $user->subscription_expires_at->isFuture() 
            ? $user->subscription_expires_at->addDays($subscription->duration_days)
            : now()->addDays($subscription->duration_days);

        $user->update([
            'subscription_type' => 'premium',
            'subscription_expires_at' => $subscriptionExpiresAt,
            'daily_ai_used' => 0, // Reset quota
        ]);

        Log::info('Premium subscription activated for user: ' . $user->id);
    }

    private function handlePaymentFailed($payload)
    {
        $paymentId = $payload['payment']['entity']['id'];
        $orderId = $payload['payment']['entity']['order_id'];

        $subscription = Subscription::where('payment_data->order_id', $orderId)
            ->orWhere('payment_data->payment_id', $paymentId)
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'failed',
                'payment_data' => array_merge($subscription->payment_data ?? [], [
                    'failure_reason' => $payload['payment']['entity']['error_description'] ?? 'Payment failed',
                ])
            ]);

            Log::info('Payment failed for subscription: ' . $subscription->id);
        }
    }

    private function handleSubscriptionCancelled($payload)
    {
        $subscriptionId = $payload['subscription']['entity']['id'];

        $subscription = Subscription::where('payment_data->razorpay_subscription_id', $subscriptionId)
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            Log::info('Subscription cancelled: ' . $subscription->id);
        }
    }

    private function handleUserCreated($data)
    {
        $firebaseUid = $data['uid'];
        $email = $data['email'] ?? null;
        $name = $data['displayName'] ?? 'Unknown User';

        // Check if user already exists
        $existingUser = User::where('firebase_uid', $firebaseUid)
            ->orWhere('email', $email)
            ->first();

        if (!$existingUser && $email) {
            User::create([
                'name' => $name,
                'email' => $email,
                'firebase_uid' => $firebaseUid,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            Log::info('User created from Firebase webhook: ' . $email);
        }
    }

    private function handleUserDeleted($data)
    {
        $firebaseUid = $data['uid'];

        $user = User::where('firebase_uid', $firebaseUid)->first();
        if ($user) {
            $user->update(['is_active' => false]);
            Log::info('User deactivated from Firebase webhook: ' . $user->email);
        }
    }
}