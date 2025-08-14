@extends('admin.layouts.app')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center">
        <a href="{{ route('admin.users.index') }}" class="mr-4 text-admin-600 hover:text-admin-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-admin-800">Edit User</h1>
            <p class="mt-1 text-sm text-admin-600">Update user information</p>
        </div>
    </div>

    <!-- Form -->
    <div class="max-w-3xl">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="admin-card">
                <h2 class="text-lg font-medium text-admin-900 mb-4">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="admin-label required">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               class="admin-input @error('name') border-danger-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="admin-label required">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                               class="admin-input @error('email') border-danger-300 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="admin-label">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                               class="admin-input @error('phone') border-danger-300 @enderror">
                        @error('phone')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="mobile" class="admin-label">Mobile</label>
                        <input type="text" name="mobile" id="mobile" value="{{ old('mobile', $user->mobile) }}"
                               class="admin-input @error('mobile') border-danger-300 @enderror">
                        @error('mobile')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <h2 class="text-lg font-medium text-admin-900 mb-4">Security</h2>
                <p class="text-sm text-admin-600 mb-4">Leave password fields empty to keep current password</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="admin-label">New Password</label>
                        <input type="password" name="password" id="password"
                               class="admin-input @error('password') border-danger-300 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="admin-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="admin-input">
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <h2 class="text-lg font-medium text-admin-900 mb-4">Account Settings</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="subscription_type" class="admin-label">Subscription Type</label>
                        <select name="subscription_type" id="subscription_type" 
                                class="admin-input @error('subscription_type') border-danger-300 @enderror">
                            <option value="free" {{ old('subscription_type', $user->subscription_type) == 'free' ? 'selected' : '' }}>Free</option>
                            <option value="premium" {{ old('subscription_type', $user->subscription_type) == 'premium' ? 'selected' : '' }}>Premium</option>
                        </select>
                        @error('subscription_type')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center space-x-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="email_verified" value="1" 
                                   {{ old('email_verified', $user->email_verified_at ? '1' : '0') ? 'checked' : '' }}
                                   class="rounded border-admin-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-admin-700">Email Verified</span>
                        </label>
                    </div>

                    @if($user->subscription_type === 'premium' && $user->subscription_expires_at)
                    <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <p class="text-sm text-purple-800">
                            Current premium subscription expires on: 
                            <strong>{{ $user->subscription_expires_at->format('M d, Y') }}</strong>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="admin-card">
                <h2 class="text-lg font-medium text-admin-900 mb-4">Account Information</h2>
                
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-admin-500">User ID</dt>
                        <dd class="mt-1 text-sm text-admin-900">#{{ $user->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-admin-500">Joined</dt>
                        <dd class="mt-1 text-sm text-admin-900">{{ $user->created_at->format('M d, Y h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-admin-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-admin-900">{{ $user->updated_at->format('M d, Y h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-admin-500">Daily AI Quota</dt>
                        <dd class="mt-1 text-sm text-admin-900">{{ $user->daily_ai_used }}/{{ $user->daily_ai_quota }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.users.show', $user) }}" class="admin-btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="admin-btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection