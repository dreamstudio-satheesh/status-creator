<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPremiumUser
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

        if (!$user->isPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a premium subscription',
                'subscription_type' => $user->subscription_type,
                'subscription_expires_at' => $user->subscription_expires_at,
            ], 403);
        }

        return $next($request);
    }
}