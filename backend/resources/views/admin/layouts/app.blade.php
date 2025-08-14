<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Tamil Status Creator') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])

    <!-- Additional styles -->
    @stack('styles')
    
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            background-attachment: fixed;
        }
        
        .mesh-bg {
            background: radial-gradient(at 40% 20%, rgba(14, 165, 233, 0.05) 0px, transparent 50%), 
                        radial-gradient(at 80% 0%, rgba(124, 58, 237, 0.05) 0px, transparent 50%), 
                        radial-gradient(at 0% 50%, rgba(236, 72, 153, 0.05) 0px, transparent 50%);
        }
        
        .floating-orb {
            position: fixed;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(124, 58, 237, 0.1));
            backdrop-filter: blur(20px);
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }
        
        .floating-orb:nth-child(1) {
            width: 200px;
            height: 200px;
            top: 10%;
            right: 10%;
            animation-delay: -5s;
        }
        
        .floating-orb:nth-child(2) {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 5%;
            animation-delay: -10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0px, 0px) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }
        
        .content-wrapper {
            position: relative;
            z-index: 10;
        }
        
        /* Sidebar slide animation */
        .sidebar-enter {
            animation: slideInLeft 0.3s ease-out;
        }
        
        @keyframes slideInLeft {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
    </style>
</head>
<body class="h-full font-sans antialiased mesh-bg">
    <!-- Floating background orbs -->
    <div class="floating-orb"></div>
    <div class="floating-orb"></div>
    
    <div class="h-full flex content-wrapper">
        <!-- Mobile sidebar overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden opacity-0 pointer-events-none transition-all duration-300"></div>
        
        <!-- Sidebar -->
        <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 sidebar-glass transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:flex lg:flex-col">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-center h-20 px-6 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-primary opacity-90"></div>
                    <div class="relative flex items-center space-x-3">
                        <div class="relative">
                            <div class="absolute inset-0 bg-white/20 rounded-xl blur-sm"></div>
                            <div class="relative bg-white/10 backdrop-blur-sm border border-white/20 p-2 rounded-xl">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-white">
                            <h1 class="text-xl font-bold tracking-tight">
                                Tamil Status
                            </h1>
                            <p class="text-xs text-white/80 font-medium">
                                Admin Dashboard
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 admin-scroll overflow-y-auto">
                    <div class="space-y-2">
                        <!-- Dashboard -->
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link-premium {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>

                        <!-- Users -->
                        <a href="{{ route('admin.users.index') }}" class="sidebar-link-premium {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            <span>Users</span>
                            <div class="ml-auto">
                                <span class="badge-info-glass">1.2k</span>
                            </div>
                        </a>

                        <!-- Themes -->
                        <a href="{{ route('admin.themes.index') }}" class="sidebar-link-premium {{ request()->routeIs('admin.themes.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"/>
                            </svg>
                            <span>Themes</span>
                        </a>

                        <!-- Templates -->
                        <a href="{{ route('admin.templates.index') }}" class="sidebar-link-premium {{ request()->routeIs('admin.templates.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span>Templates</span>
                        </a>

                        <!-- Separator -->
                        <div class="my-4 border-t border-slate-200/20"></div>

                        <!-- AI Management -->
                        <a href="{{ route('admin.ai.index') }}" class="sidebar-link-premium {{ request()->routeIs('admin.ai.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <span>AI Management</span>
                            <div class="ml-auto">
                                <div class="w-2 h-2 bg-success-400 rounded-full animate-pulse"></div>
                            </div>
                        </a>

                        <!-- Analytics -->
                        <a href="{{ route('admin.analytics') }}" class="sidebar-link-premium {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span>Analytics</span>
                        </a>

                        <!-- Feedback -->
                        <a href="{{ route('admin.feedback.index') }}" class="sidebar-link-premium {{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span>Feedback</span>
                        </a>

                        @can('manage_admins')
                        <!-- Separator -->
                        <div class="my-4 border-t border-slate-200/20"></div>
                        
                        <!-- Admin Management -->
                        <a href="{{ route('admin.admins.index') }}" class="sidebar-link-premium {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Admin Management</span>
                        </a>
                        @endcan
                    </div>
                </nav>

                <!-- User profile -->
                <div class="flex-shrink-0 p-4 border-t border-slate-200/20">
                    <div class="flex items-center space-x-3 p-3 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10 hover:bg-white/10 transition-colors duration-200">
                        <div class="avatar-glow w-10 h-10 bg-gradient-primary rounded-full flex items-center justify-center">
                            <span class="text-sm font-bold text-white">
                                {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-700 truncate">
                                {{ Auth::guard('admin')->user()->name }}
                            </p>
                            <p class="text-xs text-slate-500 truncate">
                                {{ Auth::guard('admin')->user()->display_role }}
                            </p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div id="main-content" class="flex-1 flex flex-col min-w-0 lg:pl-0">
            <!-- Top header -->
            <header class="backdrop-blur-premium bg-white/80 border-b border-slate-200/50 flex-shrink-0 sticky top-0 z-30">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <!-- Mobile menu button -->
                            <button id="sidebar-toggle" type="button" class="lg:hidden -ml-0.5 -mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100/50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 transition-all duration-200">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            <!-- Page title with breadcrumb -->
                            <div class="ml-4 lg:ml-0">
                                <h1 class="text-2xl font-bold text-slate-900">
                                    @yield('page_title', 'Dashboard')
                                </h1>
                                <p class="text-sm text-slate-600 mt-0.5">
                                    @yield('page_subtitle', 'Manage your Tamil Status Creator platform')
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <!-- Search -->
                            <div class="hidden md:block">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <input type="text" placeholder="Search..." class="input-premium pl-10 pr-4 py-2 w-64 text-sm">
                                </div>
                            </div>
                            
                            <!-- Notifications -->
                            <button type="button" class="relative p-2 rounded-xl bg-white/50 border border-slate-200/50 text-slate-500 hover:text-slate-700 hover:bg-white/70 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all duration-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5V9a6 6 0 10-12 0v3l-5 5h5m7 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span class="absolute top-0 right-0 block h-2 w-2 bg-danger-400 rounded-full ring-2 ring-white"></span>
                            </button>

                            <!-- Profile dropdown -->
                            <div class="relative">
                                <button type="button" class="flex items-center space-x-2 p-2 rounded-xl bg-white/50 border border-slate-200/50 hover:bg-white/70 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all duration-200" id="user-menu-button">
                                    <div class="w-8 h-8 bg-gradient-primary rounded-full flex items-center justify-center">
                                        <span class="text-sm font-semibold text-white">
                                            {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                                    </svg>
                                </button>

                                <!-- Dropdown menu -->
                                <div class="hidden dropdown-premium" id="user-menu">
                                    <a href="#" class="dropdown-item-premium">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Your Profile
                                    </a>
                                    <a href="#" class="dropdown-item-premium">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Settings
                                    </a>
                                    <hr class="my-1 border-slate-200">
                                    <form method="POST" action="{{ route('admin.logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item-premium w-full text-left text-danger-600 hover:text-danger-700">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto">
                <div class="py-8 px-4 sm:px-6 lg:px-8">
                    <!-- Success/Error Messages with premium styling -->
                    @if(session('success'))
                        <div class="mb-6 toast-premium border-l-4 border-l-success-500 animate-slide-down">
                            <div class="flex items-center p-4">
                                <svg class="h-5 w-5 text-success-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-slate-700 font-medium">{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 toast-premium border-l-4 border-l-danger-500 animate-slide-down">
                            <div class="flex items-center p-4">
                                <svg class="h-5 w-5 text-danger-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-slate-700 font-medium">{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 toast-premium border-l-4 border-l-danger-500 animate-slide-down">
                            <div class="p-4">
                                <div class="flex items-center mb-2">
                                    <svg class="h-5 w-5 text-danger-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-slate-700 font-medium">Please fix the following errors:</span>
                                </div>
                                <ul class="list-disc list-inside text-sm text-slate-600 ml-8 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Page content with animation -->
                    <div class="animate-fade-in">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    @stack('scripts')

    <script>
        // Enhanced sidebar toggle with animations
        document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('sidebar-enter');
                overlay.classList.remove('opacity-0', 'pointer-events-none');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0', 'pointer-events-none');
            }
            
            // Remove animation class after animation completes
            setTimeout(() => {
                sidebar.classList.remove('sidebar-enter');
            }, 300);
        });

        // Enhanced dropdown toggle with animations
        document.getElementById('user-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const button = document.getElementById('user-menu-button');
            const menu = document.getElementById('user-menu');
            
            if (button && menu && !button.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

        // Close sidebar when clicking overlay
        document.getElementById('sidebar-overlay')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('opacity-0', 'pointer-events-none');
        });

        // Auto-hide toast messages
        document.querySelectorAll('.toast-premium').forEach(toast => {
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        });

        // Add smooth scrolling to sidebar links
        document.querySelectorAll('.sidebar-link-premium').forEach(link => {
            link.addEventListener('click', function() {
                // Add loading state if needed
                if (!this.classList.contains('active')) {
                    this.style.opacity = '0.7';
                    this.style.transform = 'translateX(2px)';
                }
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.querySelector('input[placeholder="Search..."]')?.focus();
            }
            
            // Escape to close modals/dropdowns
            if (e.key === 'Escape') {
                document.getElementById('user-menu')?.classList.add('hidden');
                document.getElementById('sidebar')?.classList.add('-translate-x-full');
                document.getElementById('sidebar-overlay')?.classList.add('opacity-0', 'pointer-events-none');
            }
        });
    </script>
</body>
</html>