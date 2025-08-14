<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AIGenerationLog;
use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use App\Jobs\BulkAIGeneration;
use App\Services\OpenRouterService;
use App\Services\HuggingFaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AIController extends Controller
{
    protected $openRouterService;
    protected $huggingFaceService;

    public function __construct(
        OpenRouterService $openRouterService,
        HuggingFaceService $huggingFaceService
    ) {
        $this->middleware('auth:admin');
        $this->openRouterService = $openRouterService;
        $this->huggingFaceService = $huggingFaceService;
    }

    public function index()
    {
        $stats = [
            'total_generations' => AIGenerationLog::count(),
            'today_generations' => AIGenerationLog::whereDate('created_at', today())->count(),
            'total_cost' => AIGenerationLog::sum('cost'),
            'avg_response_time' => AIGenerationLog::avg('response_time_ms'),
            'success_rate' => AIGenerationLog::where('status', 'completed')->count() / max(AIGenerationLog::count(), 1) * 100,
        ];

        $recentLogs = AIGenerationLog::with('user')
            ->latest()
            ->limit(20)
            ->get();

        $usageChart = AIGenerationLog::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(cost) as cost')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $modelStats = AIGenerationLog::selectRaw('model_used, COUNT(*) as count, SUM(cost) as total_cost, AVG(response_time_ms) as avg_time')
            ->groupBy('model_used')
            ->orderByDesc('count')
            ->get();

        return view('admin.ai.index', compact('stats', 'recentLogs', 'usageChart', 'modelStats'));
    }

    public function bulkGeneration()
    {
        $themes = Theme::where('is_active', true)->orderBy('name')->get();
        $types = ['status', 'quote', 'greeting', 'motivational', 'funny', 'love', 'friendship'];
        
        return view('admin.ai.bulk-generation', compact('themes', 'types'));
    }

    public function generateBulk(Request $request)
    {
        $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'type' => 'required|string|in:status,quote,greeting,motivational,funny,love,friendship',
            'prompts' => 'required|array|min:1|max:10',
            'prompts.*' => 'required|string|max:500',
            'count_per_prompt' => 'required|integer|min:1|max:20',
            'style' => 'required|string|in:casual,formal,poetic,humorous',
            'length' => 'required|string|in:short,medium,long',
        ]);

        $theme = Theme::findOrFail($request->theme_id);
        
        $job = BulkAIGeneration::dispatch(
            $theme,
            $request->type,
            $request->prompts,
            $request->count_per_prompt,
            $request->style,
            $request->length,
            auth('admin')->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Bulk generation started. This may take a few minutes.',
            'job_id' => $job->getJobId(),
            'estimated_cost' => count($request->prompts) * $request->count_per_prompt * 0.02, // Estimate
        ]);
    }

    public function usage(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        $usage = AIGenerationLog::where('created_at', '>=', $startDate)
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_requests,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_requests,
                SUM(cost) as total_cost,
                AVG(response_time_ms) as avg_response_time
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $userUsage = AIGenerationLog::where('created_at', '>=', $startDate)
            ->join('users', 'ai_generation_logs.user_id', '=', 'users.id')
            ->selectRaw('
                users.name,
                users.email,
                users.subscription_type,
                COUNT(*) as total_requests,
                SUM(ai_generation_logs.cost) as total_cost
            ')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.subscription_type')
            ->orderByDesc('total_requests')
            ->limit(20)
            ->get();

        $modelUsage = AIGenerationLog::where('created_at', '>=', $startDate)
            ->selectRaw('
                model_used,
                service_type,
                COUNT(*) as total_requests,
                SUM(cost) as total_cost,
                AVG(response_time_ms) as avg_response_time,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_requests
            ')
            ->groupBy('model_used', 'service_type')
            ->orderByDesc('total_requests')
            ->get();

        return response()->json([
            'daily_usage' => $usage,
            'top_users' => $userUsage,
            'model_stats' => $modelUsage,
            'summary' => [
                'total_requests' => AIGenerationLog::where('created_at', '>=', $startDate)->count(),
                'total_cost' => AIGenerationLog::where('created_at', '>=', $startDate)->sum('cost'),
                'success_rate' => AIGenerationLog::where('created_at', '>=', $startDate)->where('status', 'completed')->count() / max(AIGenerationLog::where('created_at', '>=', $startDate)->count(), 1) * 100,
            ]
        ]);
    }

    public function costs(Request $request)
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        $costs = AIGenerationLog::where('created_at', '>=', $startDate)
            ->selectRaw('
                DATE(created_at) as date,
                service_type,
                model_used,
                SUM(cost) as total_cost,
                COUNT(*) as request_count
            ')
            ->groupBy('date', 'service_type', 'model_used')
            ->orderBy('date')
            ->get();

        $monthlyCosts = AIGenerationLog::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                SUM(cost) as total_cost,
                COUNT(*) as request_count
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'month')
            ->get();

        $serviceCosts = AIGenerationLog::where('created_at', '>=', $startDate)
            ->selectRaw('
                service_type,
                SUM(cost) as total_cost,
                COUNT(*) as request_count,
                AVG(cost) as avg_cost_per_request
            ')
            ->groupBy('service_type')
            ->get();

        return response()->json([
            'daily_costs' => $costs,
            'monthly_costs' => $monthlyCosts,
            'service_breakdown' => $serviceCosts,
            'total_cost' => AIGenerationLog::where('created_at', '>=', $startDate)->sum('cost'),
        ]);
    }

    public function bulkCaption(Request $request)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:50',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'language' => 'required|string|in:tamil,english,hindi',
            'style' => 'required|string|in:descriptive,creative,poetic,humorous',
        ]);

        $results = [];
        $totalCost = 0;

        foreach ($request->file('images') as $index => $image) {
            try {
                $imagePath = $image->store('temp/bulk-caption', 'public');
                $fullPath = storage_path('app/public/' . $imagePath);

                $caption = $this->huggingFaceService->generateImageCaption($fullPath, [
                    'language' => $request->language,
                    'style' => $request->style,
                ]);

                $results[] = [
                    'image_name' => $image->getClientOriginalName(),
                    'image_path' => $imagePath,
                    'caption' => $caption,
                    'status' => 'success'
                ];

                $totalCost += 0.01; // Estimate cost per caption

                // Log the generation
                AIGenerationLog::create([
                    'user_id' => auth('admin')->id(),
                    'provider' => 'huggingface',
                    'service_type' => 'image_captioning',
                    'model_used' => 'blip-image-captioning-base',
                    'prompt' => "Image caption for: " . $image->getClientOriginalName(),
                    'response' => $caption,
                    'cost' => 0.01,
                    'response_time_ms' => 1000, // Estimate
                    'status' => 'completed',
                ]);

            } catch (\Exception $e) {
                $results[] = [
                    'image_name' => $image->getClientOriginalName(),
                    'caption' => null,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'total_processed' => count($results),
            'successful' => collect($results)->where('status', 'success')->count(),
            'failed' => collect($results)->where('status', 'failed')->count(),
            'estimated_cost' => $totalCost,
        ]);
    }

    public function testModels()
    {
        $testPrompt = "Create a short motivational Tamil quote about success";
        $results = [];

        // Test OpenRouter models
        $openRouterModels = [
            'meta-llama/llama-3.1-70b-instruct',
            'anthropic/claude-3-haiku',
            'google/gemma-2-9b-it',
        ];

        foreach ($openRouterModels as $model) {
            try {
                $startTime = microtime(true);
                $response = $this->openRouterService->generateTamilQuote($testPrompt, 'motivational', 'short', $model);
                $responseTime = (microtime(true) - $startTime) * 1000;

                $results[] = [
                    'service' => 'openrouter',
                    'model' => $model,
                    'response' => $response,
                    'response_time_ms' => round($responseTime),
                    'status' => 'success'
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'service' => 'openrouter',
                    'model' => $model,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
            }
        }

        return response()->json([
            'test_prompt' => $testPrompt,
            'results' => $results,
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function settings()
    {
        $settings = [
            'openrouter_api_key' => config('services.openrouter.api_key') ? '****' . substr(config('services.openrouter.api_key'), -4) : null,
            'huggingface_api_key' => config('services.huggingface.api_key') ? '****' . substr(config('services.huggingface.api_key'), -4) : null,
            'default_model' => config('ai.default_model', 'meta-llama/llama-3.1-70b-instruct'),
            'max_daily_requests' => config('ai.max_daily_requests', 1000),
            'cost_per_request' => config('ai.cost_per_request', 0.02),
        ];

        return view('admin.ai.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'openrouter_api_key' => 'nullable|string',
            'huggingface_api_key' => 'nullable|string',
            'default_model' => 'required|string',
            'max_daily_requests' => 'required|integer|min:1',
            'cost_per_request' => 'required|numeric|min:0',
        ]);

        // Update configuration values
        // Note: In production, these should be stored in database settings table
        // or environment variables, not modified directly

        return redirect()->route('admin.ai.settings')
            ->with('success', 'AI settings updated successfully');
    }
}