<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = null): Response
    {
        if (!Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect()->route('admin.login');
        }

        $admin = Auth::guard('admin')->user();

        // Check if admin is active
        if (!$admin->is_active) {
            Auth::guard('admin')->logout();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account deactivated'], 403);
            }
            
            return redirect()->route('admin.login')
                ->with('error', 'Your account has been deactivated.');
        }

        // Check role if specified
        if ($role) {
            switch ($role) {
                case 'super_admin':
                    if (!$admin->isSuperAdmin()) {
                        abort(403, 'Insufficient permissions - Super Admin required');
                    }
                    break;
                case 'admin':
                    if (!$admin->isAdmin()) {
                        abort(403, 'Insufficient permissions - Admin required');
                    }
                    break;
                case 'moderator':
                    if (!$admin->isModerator() && !$admin->isAdmin()) {
                        abort(403, 'Insufficient permissions - Moderator required');
                    }
                    break;
            }
        }

        return $next($request);
    }
}