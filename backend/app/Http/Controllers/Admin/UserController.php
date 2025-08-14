<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = User::with(['subscriptions' => function($q) {
            $q->latest()->limit(1);
        }]);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->whereNotNull('email_verified_at');
                    break;
                case 'inactive':
                    $query->whereNull('email_verified_at');
                    break;
                case 'premium':
                    $query->where('subscription_type', 'premium')
                          ->where('subscription_expires_at', '>', now());
                    break;
                case 'verified':
                    $query->whereNotNull('email_verified_at');
                    break;
                case 'unverified':
                    $query->whereNull('email_verified_at');
                    break;
            }
        }

        // Filter by registration date
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => User::count(),
            'active' => User::whereNotNull('email_verified_at')->count(),
            'premium' => User::where('subscription_type', 'premium')
                ->where('subscription_expires_at', '>', now())
                ->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'today' => User::whereDate('created_at', today())->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function show(User $user)
    {
        $user->load([
            'subscriptions' => function($q) {
                $q->orderBy('created_at', 'desc');
            },
            'creations' => function($q) {
                $q->latest()->limit(10);
            },
            'feedback' => function($q) {
                $q->latest()->limit(5);
            }
        ]);

        $stats = [
            'total_creations' => $user->creations()->count(),
            'ai_usage' => $user->ai_generation_logs()->count(),
            'total_spent' => $user->subscriptions()->where('status', 'active')->sum('amount'),
            'last_login' => $user->last_login_at,
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
            'premium_until' => $request->boolean('is_premium') ? now()->addMonth() : null,
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->boolean('email_verified') && !$user->email_verified_at) {
            $updateData['email_verified_at'] = now();
        } elseif (!$request->boolean('email_verified')) {
            $updateData['email_verified_at'] = null;
        }

        if ($request->input('subscription_type') === 'premium' && $user->subscription_type !== 'premium') {
            $updateData['subscription_expires_at'] = now()->addMonth();
            $updateData['daily_ai_used'] = 0;
        } elseif ($request->input('subscription_type') === 'free') {
            $updateData['subscription_expires_at'] = null;
        }

        $user->update($updateData);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        // Hard delete user
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deactivated successfully');
    }

    public function togglePremium(User $user)
    {
        if ($user->subscription_type === 'premium' && $user->subscription_expires_at > now()) {
            $user->update([
                'is_premium' => false,
                'premium_until' => null,
            ]);
            $message = 'Premium subscription removed';
        } else {
            $user->update([
                'is_premium' => true,
                'premium_until' => now()->addMonth(),
                'ai_quota_used' => 0,
            ]);
            $message = 'Premium subscription activated';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_premium' => ($user->subscription_type === 'premium' && $user->subscription_expires_at > now())
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,make_premium,remove_premium,verify_email',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $users = User::whereIn('id', $request->user_ids);

        switch ($request->action) {
            case 'activate':
                $users->update(['email_verified_at' => now()]);
                $message = 'Users activated successfully';
                break;
            case 'deactivate':
                $users->update(['email_verified_at' => null]);
                $message = 'Users deactivated successfully';
                break;
            case 'make_premium':
                $users->update([
                    'is_premium' => true,
                    'premium_until' => now()->addMonth(),
                    'ai_quota_used' => 0
                ]);
                $message = 'Users upgraded to premium successfully';
                break;
            case 'remove_premium':
                $users->update([
                    'is_premium' => false,
                    'premium_until' => null
                ]);
                $message = 'Premium removed from users successfully';
                break;
            case 'delete':
                $users->delete();
                $message = 'Users deleted successfully';
                break;
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }
}