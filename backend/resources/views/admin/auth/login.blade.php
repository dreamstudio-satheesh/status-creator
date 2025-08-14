<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-admin-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Login - {{ config('app.name', 'Tamil Status Creator') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/admin.css'])
</head>
<body class="h-full">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <!-- Logo -->
                <div class="flex justify-center">
                    <div class="bg-primary-600 text-white p-4 rounded-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <h2 class="mt-6 text-center text-3xl font-bold text-admin-900">
                    Admin Panel
                </h2>
                <p class="mt-2 text-center text-sm text-admin-600">
                    Tamil Status Creator
                </p>
            </div>

            <!-- Login Form -->
            <form class="mt-8 space-y-6" action="{{ route('admin.login') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="email" class="admin-label">
                            Email address
                        </label>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               value="{{ old('email') }}"
                               class="admin-input @error('email') border-danger-300 focus:ring-danger-500 @enderror"
                               placeholder="Enter your email">
                        @error('email')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="admin-label">
                            Password
                        </label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                               class="admin-input @error('password') border-danger-300 focus:ring-danger-500 @enderror"
                               placeholder="Enter your password">
                        @error('password')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-admin-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-admin-700">
                            Remember me
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full admin-btn-primary py-3 text-base">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Sign in to Admin Panel
                    </button>
                </div>
            </form>

            <!-- Demo Credentials (for development) -->
            @if(app()->environment('local'))
            <div class="mt-6 p-4 bg-warning-50 border border-warning-200 rounded-lg">
                <h3 class="text-sm font-medium text-warning-800 mb-2">Demo Credentials:</h3>
                <div class="text-xs text-warning-700 space-y-1">
                    <div><strong>Super Admin:</strong> admin@tamilstatus.app / admin123</div>
                    <div><strong>Admin:</strong> admin.user@tamilstatus.app / admin123</div>
                    <div><strong>Moderator:</strong> moderator@tamilstatus.app / admin123</div>
                </div>
            </div>
            @endif

            <!-- Error Messages -->
            @if(session('error'))
                <div class="mt-4 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Success Messages -->
            @if(session('success'))
                <div class="mt-4 bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>