<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Login - {{ config('app.name', 'Tamil Status Creator') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/admin.css'])
    
    <style>
        body {
            background: linear-gradient(-45deg, #0ea5e9, #7c3aed, #ec4899, #06b6d4, #10b981);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }
        
        .floating-shapes::before,
        .floating-shapes::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .floating-shapes::before {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
            animation: float 20s ease-in-out infinite;
        }
        
        .floating-shapes::after {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
            animation: float 25s ease-in-out infinite reverse;
        }
        
        .glass-container {
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body class="h-full font-sans antialiased">
    <!-- Floating background shapes -->
    <div class="floating-shapes"></div>
    
    <!-- Main login container -->
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 glass-container">
        <div class="max-w-md w-full">
            <!-- Login card with glassmorphism -->
            <div class="glass-card p-8 animate-fade-in">
                <!-- Logo and branding -->
                <div class="text-center mb-8">
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-primary rounded-2xl blur-lg opacity-50 animate-glow"></div>
                            <div class="relative bg-gradient-primary text-white p-4 rounded-2xl shadow-premium">
                                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <h2 class="text-3xl font-bold text-white mb-2 tracking-tight">
                        Welcome Back
                    </h2>
                    <p class="text-white/80 text-base font-medium">
                        Tamil Status Creator Admin
                    </p>
                    
                    <!-- Decorative line -->
                    <div class="flex justify-center mt-4">
                        <div class="w-20 h-1 bg-gradient-to-r from-transparent via-white/50 to-transparent rounded-full"></div>
                    </div>
                </div>

                <!-- Login Form -->
                <form class="space-y-6 animate-slide-up" action="{{ route('admin.login') }}" method="POST">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label for="email" class="label-premium text-white/90">
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </div>
                                <input id="email" name="email" type="email" autocomplete="email" required 
                                       value="{{ old('email') }}"
                                       class="input-glass pl-10 @error('email') border-danger-300 focus:ring-danger-500/50 @enderror"
                                       placeholder="admin@example.com">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-300 flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="label-premium text-white/90">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input id="password" name="password" type="password" autocomplete="current-password" required 
                                       class="input-glass pl-10 @error('password') border-danger-300 focus:ring-danger-500/50 @enderror"
                                       placeholder="••••••••">
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-300 flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" 
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500/50 border-white/30 rounded bg-white/20 backdrop-blur-sm">
                            <label for="remember" class="ml-3 block text-sm font-medium text-white/80">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="text-sm">
                            <a href="#" class="font-medium text-white/80 hover:text-white transition-colors duration-200">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn-primary-glass w-full py-4 text-base font-semibold group">
                            <svg class="w-5 h-5 mr-2 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Sign in to Dashboard
                            <div class="absolute inset-0 bg-white/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </button>
                    </div>
                </form>

                <!-- Demo Credentials (for development) -->
                @if(app()->environment('local'))
                <div class="mt-8 p-4 glass-card border border-accent-200/30 bg-accent-50/10">
                    <h3 class="text-sm font-semibold text-white mb-3 flex items-center">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Demo Access
                    </h3>
                    <div class="text-xs text-white/80 space-y-2">
                        <div class="flex justify-between items-center p-2 bg-white/5 rounded-lg backdrop-blur-sm">
                            <span class="font-medium">Super Admin:</span>
                            <code class="text-accent-300">admin@example.com / admin123</code>
                        </div>
                        <div class="flex justify-between items-center p-2 bg-white/5 rounded-lg backdrop-blur-sm">
                            <span class="font-medium">Admin:</span>
                            <code class="text-accent-300">admin.user@example.com / admin123</code>
                        </div>
                        <div class="flex justify-between items-center p-2 bg-white/5 rounded-lg backdrop-blur-sm">
                            <span class="font-medium">Moderator:</span>
                            <code class="text-accent-300">moderator@example.com / admin123</code>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Status Messages -->
            @if(session('error'))
                <div class="mt-6 p-4 glass-card border border-danger-300/50 bg-danger-50/10 animate-slide-up">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-danger-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-white font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mt-6 p-4 glass-card border border-success-300/50 bg-success-50/10 animate-slide-up">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-success-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-white font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 glass-container">
        <div class="glass-card px-4 py-2 text-center">
            <p class="text-xs text-white/60 font-medium">
                © {{ date('Y') }} Tamil Status Creator. Powered by AI & Innovation.
            </p>
        </div>
    </div>
</body>
</html>