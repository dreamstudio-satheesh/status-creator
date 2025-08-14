<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:general,bug,feature_request,support',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'rating' => 'nullable|integer|between:1,5',
            'metadata' => 'nullable|array',
            'metadata.device_info' => 'nullable|string|max:500',
            'metadata.app_version' => 'nullable|string|max:50',
            'metadata.os_version' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        $feedback = DB::table('user_feedback')->insertGetId([
            'user_id' => $user->id,
            'type' => $request->type,
            'subject' => $request->subject,
            'message' => $request->message,
            'rating' => $request->rating,
            'metadata' => $request->metadata ? json_encode($request->metadata) : null,
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully. We will review it and get back to you.',
            'feedback_id' => $feedback,
        ], 201);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = DB::table('user_feedback')
                  ->where('user_id', $user->id);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $feedback = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'feedback' => $feedback->items(),
            'pagination' => [
                'current_page' => $feedback->currentPage(),
                'last_page' => $feedback->lastPage(),
                'per_page' => $feedback->perPage(),
                'total' => $feedback->total(),
                'has_more' => $feedback->hasMorePages(),
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $feedback = DB::table('user_feedback')
                     ->where('id', $id)
                     ->where('user_id', $user->id)
                     ->first();

        if (!$feedback) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'feedback' => [
                'id' => $feedback->id,
                'type' => $feedback->type,
                'subject' => $feedback->subject,
                'message' => $feedback->message,
                'rating' => $feedback->rating,
                'status' => $feedback->status,
                'admin_response' => $feedback->admin_response,
                'responded_at' => $feedback->responded_at,
                'metadata' => $feedback->metadata ? json_decode($feedback->metadata, true) : null,
                'created_at' => $feedback->created_at,
                'updated_at' => $feedback->updated_at,
            ],
        ]);
    }

    public function faq()
    {
        $faq = [
            [
                'id' => 1,
                'category' => 'Getting Started',
                'question' => 'How do I create my first Tamil status?',
                'answer' => 'Select a theme, choose a template, customize the text and styling, then save or share your creation.',
                'order' => 1,
            ],
            [
                'id' => 2,
                'category' => 'Premium Features',
                'question' => 'What features do I get with premium subscription?',
                'answer' => 'Premium users get access to exclusive templates, higher AI generation quota, no watermarks, and priority support.',
                'order' => 2,
            ],
            [
                'id' => 3,
                'category' => 'AI Generation',
                'question' => 'How does AI quote generation work?',
                'answer' => 'Our AI generates unique Tamil quotes based on your selected theme and preferences. Premium users get 100 generations per day vs 10 for free users.',
                'order' => 3,
            ],
            [
                'id' => 4,
                'category' => 'Account Management',
                'question' => 'How do I reset my password?',
                'answer' => 'Go to Settings > Account > Change Password, or use the forgot password option on the login screen.',
                'order' => 4,
            ],
            [
                'id' => 5,
                'category' => 'Sharing',
                'question' => 'Can I share my creations on social media?',
                'answer' => 'Yes! You can directly share to WhatsApp, Instagram, Facebook, or save to your gallery.',
                'order' => 5,
            ],
            [
                'id' => 6,
                'category' => 'Technical Issues',
                'question' => 'The app is running slowly. What should I do?',
                'answer' => 'Try closing other apps, clearing app cache, or restarting your device. Contact support if issues persist.',
                'order' => 6,
            ],
        ];

        return response()->json([
            'success' => true,
            'faq' => $faq,
            'categories' => array_unique(array_column($faq, 'category')),
        ]);
    }

    public function contactInfo()
    {
        return response()->json([
            'success' => true,
            'contact' => [
                'email' => 'support@tamilstatus.app',
                'phone' => '+91-9876543210',
                'whatsapp' => '+91-9876543210',
                'website' => 'https://tamilstatus.app',
                'support_hours' => 'Monday to Friday, 9 AM to 6 PM IST',
                'response_time' => [
                    'general' => '24-48 hours',
                    'bug' => '12-24 hours',
                    'premium_support' => '2-4 hours',
                ],
            ],
            'social_media' => [
                'facebook' => 'https://facebook.com/tamilstatusapp',
                'instagram' => 'https://instagram.com/tamilstatusapp',
                'twitter' => 'https://twitter.com/tamilstatusapp',
                'youtube' => 'https://youtube.com/tamilstatusapp',
            ],
        ]);
    }

    public function submitAppRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        $existingRating = DB::table('user_feedback')
                           ->where('user_id', $user->id)
                           ->where('type', 'app_rating')
                           ->first();

        if ($existingRating) {
            DB::table('user_feedback')
              ->where('id', $existingRating->id)
              ->update([
                  'rating' => $request->rating,
                  'message' => $request->review ?? 'App rating update',
                  'updated_at' => now(),
              ]);

            $message = 'App rating updated successfully';
        } else {
            DB::table('user_feedback')->insert([
                'user_id' => $user->id,
                'type' => 'app_rating',
                'subject' => 'App Rating',
                'message' => $request->review ?? 'App rating submission',
                'rating' => $request->rating,
                'status' => 'closed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $message = 'Thank you for rating our app!';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}