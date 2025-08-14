<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAIQuota
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if ($user->last_quota_reset !== today()) {
            $user->update([
                'daily_ai_used' => 0,
                'last_quota_reset' => today(),
            ]);
            $user->refresh();
        }

        if ($user->daily_ai_used >= $user->daily_ai_quota) {
            return response()->json([
                'success' => false,
                'message' => 'Daily AI generation quota exceeded',
                'daily_ai_quota' => $user->daily_ai_quota,
                'daily_ai_used' => $user->daily_ai_used,
                'is_premium' => $user->isPremium(),
                'suggestion' => $user->isPremium() 
                    ? 'Your daily quota will reset tomorrow' 
                    : 'Upgrade to premium for higher quota',
            ], 429);
        }

        return $next($request);
    }
}