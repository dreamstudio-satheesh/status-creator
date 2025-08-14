@extends('admin.layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center">
            <a href="{{ route('admin.users.index') }}" class="mr-4 text-admin-600 hover:text-admin-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-admin-800">User Details</h1>
                <p class="mt-1 text-sm text-admin-600">View and manage user information</p>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="admin-btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit User
            </a>
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                  class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn-danger">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete User
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Profile Card -->
        <div class="lg:col-span-1">
            <div class="admin-card">
                <div class="text-center">
                    @if($user->avatar)
                        <img class="h-24 w-24 rounded-full mx-auto" src="{{ $user->avatar }}" alt="{{ $user->name }}">
                    @else
                        <div class="h-24 w-24 rounded-full bg-primary-100 flex items-center justify-center mx-auto">
                            <span class="text-primary-600 font-bold text-3xl">
                                {{ substr($user->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <h2 class="mt-4 text-xl font-bold text-admin-900">{{ $user->name }}</h2>
                    <p class="text-sm text-admin-600">{{ $user->email }}</p>
                    @if($user->mobile)
                        <p class="text-sm text-admin-600">{{ $user->mobile }}</p>
                    @endif
                </div>

                <div class="mt-6 border-t border-admin-200 pt-6">
                    <dl class="space-y-4">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-admin-500">User ID</dt>
                            <dd class="text-sm text-admin-900">#{{ $user->id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-admin-500">Status</dt>
                            <dd>
                                @if($user->email_verified_at)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-success-100 text-success-800">
                                        Verified
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-warning-100 text-warning-800">
                                        Unverified
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-admin-500">Subscription</dt>
                            <dd>
                                @if($user->subscription_type === 'premium' && $user->subscription_expires_at && $user->subscription_expires_at->isFuture())
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Premium
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-admin-100 text-admin-800">
                                        Free
                                    </span>
                                @endif
                            </dd>
                        </div>
                        @if($user->subscription_expires_at)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-admin-500">Expires</dt>
                                <dd class="text-sm text-admin-900">{{ $user->subscription_expires_at->format('M d, Y') }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-admin-500">Joined</dt>
                            <dd class="text-sm text-admin-900">{{ $user->created_at->format('M d, Y') }}</dd>
                        </div>
                        @if($user->last_login_at ?? false)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-admin-500">Last Login</dt>
                                <dd class="text-sm text-admin-900">{{ $user->last_login_at->diffForHumans() }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="mt-6 pt-6 border-t border-admin-200">
                    <h3 class="text-sm font-medium text-admin-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <button onclick="togglePremium({{ $user->id }})" 
                                class="w-full admin-btn-secondary">
                            Toggle Premium Status
                        </button>
                        @if(!$user->email_verified_at)
                            <button onclick="verifyEmail({{ $user->id }})" 
                                    class="w-full admin-btn-secondary">
                                Verify Email
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity & Stats -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Statistics -->
            @if(isset($stats))
            <div class="admin-card">
                <h3 class="text-lg font-medium text-admin-900 mb-4">User Statistics</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-admin-500">Total Creations</p>
                        <p class="text-2xl font-bold text-admin-900">{{ $stats['total_creations'] ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-admin-500">AI Usage</p>
                        <p class="text-2xl font-bold text-admin-900">{{ $stats['ai_usage'] ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-admin-500">Total Spent</p>
                        <p class="text-2xl font-bold text-admin-900">₹{{ number_format($stats['total_spent'] ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-admin-500">Daily Quota</p>
                        <p class="text-2xl font-bold text-admin-900">
                            {{ $user->daily_ai_used }}/{{ $user->daily_ai_quota }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Creations -->
            @if(isset($user->creations) && $user->creations->count() > 0)
            <div class="admin-card">
                <h3 class="text-lg font-medium text-admin-900 mb-4">Recent Creations</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-admin-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-admin-500 uppercase">ID</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-admin-500 uppercase">Template</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-admin-500 uppercase">AI Generated</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-admin-500 uppercase">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-admin-200">
                            @foreach($user->creations as $creation)
                            <tr>
                                <td class="px-3 py-2 text-sm text-admin-900">#{{ $creation->id }}</td>
                                <td class="px-3 py-2 text-sm text-admin-900">
                                    {{ $creation->template->title ?? 'Custom' }}
                                </td>
                                <td class="px-3 py-2 text-sm">
                                    @if($creation->is_ai_generated)
                                        <span class="text-success-600">Yes</span>
                                    @else
                                        <span class="text-admin-400">No</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm text-admin-500">
                                    {{ $creation->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Recent Feedback -->
            @if(isset($user->feedback) && $user->feedback->count() > 0)
            <div class="admin-card">
                <h3 class="text-lg font-medium text-admin-900 mb-4">Recent Feedback</h3>
                <div class="space-y-4">
                    @foreach($user->feedback as $feedback)
                    <div class="border-l-4 border-primary-400 pl-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-medium text-admin-900">{{ $feedback->subject }}</h4>
                            <span class="text-xs text-admin-500">{{ $feedback->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-admin-600">{{ $feedback->message }}</p>
                        @if($feedback->rating)
                            <div class="mt-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-warning-400">
                                        @if($i <= $feedback->rating)
                                            ★
                                        @else
                                            ☆
                                        @endif
                                    </span>
                                @endfor
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Subscription History -->
            @if(isset($user->subscriptions) && $user->subscriptions->count() > 0)
            <div class="admin-card">
                <h3 class="text-lg font-medium text-admin-900 mb-4">Subscription History</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-admin-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-admin-500 uppercase">Plan</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-admin-500 uppercase">Amount</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-admin-500 uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-admin-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-admin-200">
                            @foreach($user->subscriptions as $subscription)
                            <tr>
                                <td class="px-3 py-2 text-sm text-admin-900">{{ ucfirst($subscription->plan_type) }}</td>
                                <td class="px-3 py-2 text-sm text-admin-900">₹{{ number_format($subscription->amount, 2) }}</td>
                                <td class="px-3 py-2 text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($subscription->status === 'active') bg-success-100 text-success-800
                                        @elseif($subscription->status === 'pending') bg-warning-100 text-warning-800
                                        @else bg-admin-100 text-admin-800
                                        @endif">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-sm text-admin-500">
                                    {{ $subscription->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePremium(userId) {
        if (!confirm('Toggle premium status for this user?')) return;
        
        fetch(`/admin/users/${userId}/toggle-premium`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }

    function verifyEmail(userId) {
        if (!confirm('Verify email for this user?')) return;
        
        // Implementation for email verification
        alert('Email verification functionality to be implemented');
    }
</script>
@endpush
@endsection