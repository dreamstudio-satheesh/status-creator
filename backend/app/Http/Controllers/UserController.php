<?php

namespace App\Http\Controllers;

use App\Models\UserCreation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();

        if ($user->last_quota_reset !== today()) {
            $user->update([
                'daily_ai_used' => 0,
                'last_quota_reset' => today(),
            ]);
            $user->refresh();
        }

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
                'remaining_quota' => $user->daily_ai_quota - $user->daily_ai_used,
                'is_premium' => $user->isPremium(),
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'mobile' => 'sometimes|string|regex:/^[+]?[0-9]{10,15}$/|unique:users,mobile,' . $user->id,
            'avatar' => 'sometimes|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = $request->only(['name', 'email', 'mobile', 'avatar']);
        
        if (isset($updateData['email']) && $updateData['email'] !== $user->email) {
            $updateData['email_verified_at'] = null;
        }

        $user->update($updateData);

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
                'remaining_quota' => $user->daily_ai_quota - $user->daily_ai_used,
                'is_premium' => $user->isPremium(),
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }

    public function subscription(Request $request)
    {
        $user = $request->user();

        $subscriptionHistory = DB::table('subscriptions')
                                ->where('user_id', $user->id)
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();

        $currentSubscription = $subscriptionHistory->first();

        return response()->json([
            'success' => true,
            'current_subscription' => [
                'type' => $user->subscription_type,
                'expires_at' => $user->subscription_expires_at,
                'is_premium' => $user->isPremium(),
                'days_remaining' => $user->isPremium() && $user->subscription_expires_at 
                    ? now()->diffInDays($user->subscription_expires_at, false) 
                    : null,
            ],
            'recent_payments' => $subscriptionHistory,
        ]);
    }

    public function usageStats(Request $request)
    {
        $user = $request->user();

        if ($user->last_quota_reset !== today()) {
            $user->update([
                'daily_ai_used' => 0,
                'last_quota_reset' => today(),
            ]);
            $user->refresh();
        }

        $totalCreations = UserCreation::where('user_id', $user->id)->count();
        $todayCreations = UserCreation::where('user_id', $user->id)
                                     ->whereDate('created_at', today())
                                     ->count();

        $aiGeneratedCreations = UserCreation::where('user_id', $user->id)
                                           ->where('is_ai_generated', true)
                                           ->count();

        $totalShares = UserCreation::where('user_id', $user->id)
                                  ->sum('shared_count');

        $weeklyStats = UserCreation::where('user_id', $user->id)
                                  ->whereBetween('created_at', [now()->subWeek(), now()])
                                  ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                                  ->groupBy('date')
                                  ->orderBy('date')
                                  ->get()
                                  ->pluck('count', 'date');

        $favoriteTemplates = DB::table('user_favorites')
                              ->join('templates', 'user_favorites.template_id', '=', 'templates.id')
                              ->join('themes', 'templates.theme_id', '=', 'themes.id')
                              ->where('user_favorites.user_id', $user->id)
                              ->select([
                                  'templates.id',
                                  'templates.title',
                                  'templates.background_image',
                                  'themes.name as theme_name',
                                  'themes.color as theme_color'
                              ])
                              ->limit(5)
                              ->get();

        return response()->json([
            'success' => true,
            'ai_quota' => [
                'daily_quota' => $user->daily_ai_quota,
                'daily_used' => $user->daily_ai_used,
                'remaining' => $user->daily_ai_quota - $user->daily_ai_used,
                'usage_percentage' => round(($user->daily_ai_used / $user->daily_ai_quota) * 100, 1),
                'last_reset' => $user->last_quota_reset,
            ],
            'creation_stats' => [
                'total_creations' => $totalCreations,
                'today_creations' => $todayCreations,
                'ai_generated_count' => $aiGeneratedCreations,
                'total_shares' => $totalShares,
                'weekly_stats' => $weeklyStats,
            ],
            'favorites_count' => $favoriteTemplates->count(),
            'recent_favorite_templates' => $favoriteTemplates,
        ]);
    }

    public function creations(Request $request)
    {
        $user = $request->user();

        $query = UserCreation::with('template.theme')
                            ->where('user_id', $user->id);

        if ($request->has('is_ai_generated')) {
            $query->where('is_ai_generated', $request->boolean('is_ai_generated'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('custom_text', 'LIKE', "%{$search}%")
                  ->orWhereHas('template', function ($templateQuery) use ($search) {
                      $templateQuery->where('title', 'LIKE', "%{$search}%")
                                   ->orWhere('quote_text', 'LIKE', "%{$search}%")
                                   ->orWhere('quote_text_ta', 'LIKE', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'newest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_shared':
                $query->orderBy('shared_count', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $creations = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'creations' => $creations->items(),
            'pagination' => [
                'current_page' => $creations->currentPage(),
                'last_page' => $creations->lastPage(),
                'per_page' => $creations->perPage(),
                'total' => $creations->total(),
                'has_more' => $creations->hasMorePages(),
            ],
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE_MY_ACCOUNT',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if ($user->password && !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password',
            ], 422);
        }

        try {
            DB::transaction(function () use ($user) {
                $user->tokens()->delete();
                
                $creations = UserCreation::where('user_id', $user->id)->get();
                foreach ($creations as $creation) {
                    if ($creation->image_url) {
                        Storage::delete($creation->image_url);
                    }
                }
                
                $user->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function preferences(Request $request)
    {
        $user = $request->user();

        $preferences = json_decode($user->preferences ?? '{}', true);

        return response()->json([
            'success' => true,
            'preferences' => [
                'language' => $preferences['language'] ?? 'ta',
                'theme' => $preferences['theme'] ?? 'light',
                'notifications' => [
                    'push_enabled' => $preferences['notifications']['push_enabled'] ?? true,
                    'email_enabled' => $preferences['notifications']['email_enabled'] ?? true,
                    'marketing_enabled' => $preferences['notifications']['marketing_enabled'] ?? false,
                ],
                'privacy' => [
                    'profile_public' => $preferences['privacy']['profile_public'] ?? false,
                    'creation_sharing' => $preferences['privacy']['creation_sharing'] ?? true,
                ],
                'editor' => [
                    'default_font' => $preferences['editor']['default_font'] ?? 'Tamil',
                    'default_text_color' => $preferences['editor']['default_text_color'] ?? '#FFFFFF',
                    'default_text_size' => $preferences['editor']['default_text_size'] ?? 24,
                    'default_alignment' => $preferences['editor']['default_alignment'] ?? 'center',
                ],
            ],
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'sometimes|string|in:ta,en',
            'theme' => 'sometimes|string|in:light,dark',
            'notifications.push_enabled' => 'sometimes|boolean',
            'notifications.email_enabled' => 'sometimes|boolean',
            'notifications.marketing_enabled' => 'sometimes|boolean',
            'privacy.profile_public' => 'sometimes|boolean',
            'privacy.creation_sharing' => 'sometimes|boolean',
            'editor.default_font' => 'sometimes|string|max:100',
            'editor.default_text_color' => 'sometimes|string|regex:/^#[a-fA-F0-9]{6}$/',
            'editor.default_text_size' => 'sometimes|integer|between:12,72',
            'editor.default_alignment' => 'sometimes|string|in:left,center,right',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $currentPreferences = json_decode($user->preferences ?? '{}', true);

        $newPreferences = array_merge_recursive($currentPreferences, $request->all());

        $user->update(['preferences' => json_encode($newPreferences)]);

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'preferences' => $newPreferences,
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        if ($user->last_quota_reset !== today()) {
            $user->update([
                'daily_ai_used' => 0,
                'last_quota_reset' => today(),
            ]);
            $user->refresh();
        }

        $stats = [
            'total_creations' => UserCreation::where('user_id', $user->id)->count(),
            'favorite_count' => DB::table('user_favorites')->where('user_id', $user->id)->count(),
            'ai_quota_used' => $user->daily_ai_used,
            'ai_quota_total' => $user->daily_ai_quota,
            'subscription_days_left' => $user->isPremium() && $user->subscription_expires_at 
                ? max(0, now()->diffInDays($user->subscription_expires_at, false))
                : null,
        ];

        $recentCreations = UserCreation::with('template.theme')
                                      ->where('user_id', $user->id)
                                      ->orderBy('created_at', 'desc')
                                      ->limit(5)
                                      ->get();

        $popularThemes = DB::table('user_creations')
                          ->join('templates', 'user_creations.template_id', '=', 'templates.id')
                          ->join('themes', 'templates.theme_id', '=', 'themes.id')
                          ->where('user_creations.user_id', $user->id)
                          ->select('themes.name', 'themes.name_ta', 'themes.color', DB::raw('COUNT(*) as usage_count'))
                          ->groupBy('themes.id', 'themes.name', 'themes.name_ta', 'themes.color')
                          ->orderBy('usage_count', 'desc')
                          ->limit(3)
                          ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'recent_creations' => $recentCreations,
            'popular_themes' => $popularThemes,
            'user' => [
                'name' => $user->name,
                'avatar' => $user->avatar,
                'is_premium' => $user->isPremium(),
                'subscription_type' => $user->subscription_type,
            ],
        ]);
    }
}