<?php

namespace App\Http\Controllers;

use App\Models\AIGenerationLog;
use App\Services\HuggingFaceService;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AIController extends Controller
{
    private HuggingFaceService $huggingFaceService;
    private OpenRouterService $openRouterService;

    public function __construct(
        HuggingFaceService $huggingFaceService,
        OpenRouterService $openRouterService
    ) {
        $this->huggingFaceService = $huggingFaceService;
        $this->openRouterService = $openRouterService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/ai/generate-quote",
     *     tags={"AI Generation"},
     *     summary="Generate Tamil quote using AI",
     *     description="Generates a Tamil quote using AI based on theme, style, and length preferences. Requires authentication and consumes daily quota.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="theme", type="string", example="love", description="Theme of the quote (love, motivation, life, success, etc.)"),
     *             @OA\Property(property="style", type="string", example="inspirational", enum={"inspirational", "emotional", "philosophical", "traditional", "modern", "poetic"}, description="Style of the quote"),
     *             @OA\Property(property="length", type="string", example="medium", enum={"short", "medium", "long"}, description="Length of the quote"),
     *             @OA\Property(property="context", type="string", example="for social media", description="Additional context for quote generation"),
     *             @OA\Property(property="template_id", type="integer", example=1, description="Optional template ID to associate with generation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quote generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="quote", type="string", example="அன்பு என்பது இதயத்தின் மொழி"),
     *             @OA\Property(property="theme", type="string", example="love"),
     *             @OA\Property(property="style", type="string", example="inspirational"),
     *             @OA\Property(property="length", type="string", example="medium"),
     *             @OA\Property(property="quota_remaining", type="integer", example=9),
     *             @OA\Property(property="generation_metadata", type="object",
     *                 @OA\Property(property="model_used", type="string", example="meta-llama/llama-3.2-3b-instruct:free"),
     *                 @OA\Property(property="tokens_used", type="integer", example=45),
     *                 @OA\Property(property="cost", type="number", format="float", example=0.000123),
     *                 @OA\Property(property="processing_time_ms", type="number", format="float", example=1234.56)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Quota exceeded or rate limited",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Daily AI generation quota exceeded"),
     *             @OA\Property(property="daily_ai_quota", type="integer", example=10),
     *             @OA\Property(property="daily_ai_used", type="integer", example=10),
     *             @OA\Property(property="is_premium", type="boolean", example=false),
     *             @OA\Property(property="suggestion", type="string", example="Upgrade to premium for higher quota (100 vs 10)")
     *         )
     *     )
     * )
     */
    public function generateQuote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme' => 'required|string|max:50',
            'style' => 'sometimes|string|in:inspirational,emotional,philosophical,traditional,modern,poetic',
            'length' => 'sometimes|string|in:short,medium,long',
            'context' => 'sometimes|string|max:200',
            'template_id' => 'sometimes|integer|exists:templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Check rate limiting
        $rateLimitKey = 'ai_generation:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) { // 5 per minute
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many AI generation requests. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        // Reset quota if needed
        if ($user->last_quota_reset !== today()) {
            $user->update([
                'daily_ai_used' => 0,
                'last_quota_reset' => today(),
            ]);
            $user->refresh();
        }

        // Check quota
        if ($user->daily_ai_used >= $user->daily_ai_quota) {
            return response()->json([
                'success' => false,
                'message' => 'Daily AI generation quota exceeded',
                'daily_ai_quota' => $user->daily_ai_quota,
                'daily_ai_used' => $user->daily_ai_used,
                'is_premium' => $user->isPremium(),
                'suggestion' => $user->isPremium() 
                    ? 'Your daily quota will reset tomorrow' 
                    : 'Upgrade to premium for higher quota (100 vs 10)',
            ], 429);
        }

        try {
            $result = $this->openRouterService->generateTamilQuote([
                'theme' => $request->theme,
                'style' => $request->get('style', 'inspirational'),
                'length' => $request->get('length', 'medium'),
                'context' => $request->get('context', ''),
                'user_id' => $user->id,
                'template_id' => $request->template_id,
            ]);

            if ($result['success']) {
                // Increment user's daily usage
                $user->increment('daily_ai_used');

                return response()->json([
                    'success' => true,
                    'quote' => $result['quote'],
                    'theme' => $request->theme,
                    'style' => $request->get('style', 'inspirational'),
                    'length' => $request->get('length', 'medium'),
                    'quota_remaining' => $user->daily_ai_quota - ($user->daily_ai_used + 1),
                    'generation_metadata' => $result['metadata'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate Tamil quote',
                    'error' => $result['error'],
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI service temporarily unavailable',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function captionImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_url' => 'required|url|max:500',
            'template_id' => 'sometimes|integer|exists:templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Check rate limiting
        $rateLimitKey = 'ai_caption:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) { // 3 per minute
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many image captioning requests. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        try {
            $result = $this->huggingFaceService->analyzeImageForTemplate(
                $request->image_url,
                $user->id
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'analysis' => $result['analysis'],
                    'processing_summary' => $result['processing_summary'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to analyze image',
                    'error' => $result['error'] ?? 'Unknown error',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image analysis service temporarily unavailable',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function quota(Request $request)
    {
        $user = $request->user();

        if ($user->last_quota_reset !== today()) {
            $user->update([
                'daily_ai_used' => 0,
                'last_quota_reset' => today(),
            ]);
            $user->refresh();
        }

        $todayGenerations = AIGenerationLog::where('user_id', $user->id)
                                         ->whereDate('created_at', today())
                                         ->where('status', 'success')
                                         ->count();

        $totalCostToday = AIGenerationLog::where('user_id', $user->id)
                                        ->whereDate('created_at', today())
                                        ->sum('cost');

        $weeklyStats = AIGenerationLog::where('user_id', $user->id)
                                     ->whereBetween('created_at', [now()->subWeek(), now()])
                                     ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(cost) as cost')
                                     ->groupBy('date')
                                     ->orderBy('date')
                                     ->get()
                                     ->pluck('count', 'date');

        $popularThemes = AIGenerationLog::where('user_id', $user->id)
                                       ->whereDate('created_at', '>=', now()->subMonth())
                                       ->whereNotNull('metadata')
                                       ->get()
                                       ->groupBy(function ($log) {
                                           $metadata = json_decode($log->metadata, true);
                                           return $metadata['theme'] ?? 'unknown';
                                       })
                                       ->map(function ($group) {
                                           return $group->count();
                                       })
                                       ->sortDesc()
                                       ->take(5);

        return response()->json([
            'success' => true,
            'quota_info' => [
                'daily_quota' => $user->daily_ai_quota,
                'daily_used' => $user->daily_ai_used,
                'remaining' => $user->daily_ai_quota - $user->daily_ai_used,
                'usage_percentage' => round(($user->daily_ai_used / $user->daily_ai_quota) * 100, 1),
                'last_reset' => $user->last_quota_reset,
                'next_reset' => today()->addDay()->toDateString(),
            ],
            'statistics' => [
                'today_generations' => $todayGenerations,
                'today_cost' => round($totalCostToday, 6),
                'weekly_stats' => $weeklyStats,
                'popular_themes' => $popularThemes,
            ],
            'subscription_info' => [
                'type' => $user->subscription_type,
                'is_premium' => $user->isPremium(),
                'expires_at' => $user->subscription_expires_at,
            ],
        ]);
    }

    public function models(Request $request)
    {
        $cacheKey = 'ai_models_status';
        
        $modelsStatus = Cache::remember($cacheKey, 300, function () { // 5 minutes cache
            return [
                'openrouter' => [
                    'available_models' => $this->openRouterService->getAvailableModels(),
                    'connection' => $this->openRouterService->testConnection(),
                ],
                'huggingface' => [
                    'model_status' => $this->huggingFaceService->getModelStatus(),
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'services' => $modelsStatus,
            'cached_at' => now()->toISOString(),
        ]);
    }

    public function usage(Request $request)
    {
        $user = $request->user();
        $days = $request->get('days', 30);
        $days = min($days, 90); // Max 90 days

        $usageStats = AIGenerationLog::where('user_id', $user->id)
                                    ->whereDate('created_at', '>=', now()->subDays($days))
                                    ->selectRaw('
                                        DATE(created_at) as date,
                                        COUNT(*) as total_generations,
                                        SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_generations,
                                        SUM(tokens_used) as total_tokens,
                                        SUM(cost) as total_cost,
                                        AVG(cost) as avg_cost_per_generation
                                    ')
                                    ->groupBy('date')
                                    ->orderBy('date', 'desc')
                                    ->get();

        $modelUsage = AIGenerationLog::where('user_id', $user->id)
                                    ->whereDate('created_at', '>=', now()->subDays($days))
                                    ->selectRaw('model_used, COUNT(*) as usage_count, SUM(cost) as total_cost')
                                    ->groupBy('model_used')
                                    ->orderBy('usage_count', 'desc')
                                    ->get();

        $themePopularity = AIGenerationLog::where('user_id', $user->id)
                                         ->whereDate('created_at', '>=', now()->subDays($days))
                                         ->whereNotNull('metadata')
                                         ->get()
                                         ->groupBy(function ($log) {
                                             $metadata = json_decode($log->metadata, true);
                                             return $metadata['theme'] ?? 'unknown';
                                         })
                                         ->map(function ($group) {
                                             return [
                                                 'count' => $group->count(),
                                                 'total_cost' => $group->sum('cost'),
                                             ];
                                         })
                                         ->sortByDesc('count');

        return response()->json([
            'success' => true,
            'period_days' => $days,
            'daily_usage' => $usageStats,
            'model_usage' => $modelUsage,
            'theme_popularity' => $themePopularity,
            'summary' => [
                'total_generations' => $usageStats->sum('total_generations'),
                'successful_generations' => $usageStats->sum('successful_generations'),
                'total_tokens' => $usageStats->sum('total_tokens'),
                'total_cost' => round($usageStats->sum('total_cost'), 6),
                'success_rate' => $usageStats->sum('total_generations') > 0 
                    ? round(($usageStats->sum('successful_generations') / $usageStats->sum('total_generations')) * 100, 2)
                    : 0,
            ],
        ]);
    }

    public function regenerate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'generation_id' => 'required|integer|exists:ai_generation_logs,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $originalGeneration = AIGenerationLog::where('id', $request->generation_id)
                                            ->where('user_id', $user->id)
                                            ->first();

        if (!$originalGeneration) {
            return response()->json([
                'success' => false,
                'message' => 'Generation not found or not accessible',
            ], 404);
        }

        $metadata = json_decode($originalGeneration->metadata, true) ?? [];

        // Use the same parameters as the original generation
        return $this->generateQuote(new Request([
            'theme' => $metadata['theme'] ?? 'general',
            'style' => $metadata['style'] ?? 'inspirational',
            'length' => $metadata['length'] ?? 'medium',
            'template_id' => $originalGeneration->template_id,
        ]));
    }
}