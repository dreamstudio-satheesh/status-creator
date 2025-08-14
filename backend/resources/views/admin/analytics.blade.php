@extends('admin.layouts.app')

@section('title', 'Analytics & Reports')
@section('page_title', 'Analytics')
@section('page_subtitle', 'Detailed analytics and performance insights')

@section('content')
<div class="space-responsive">
    <!-- Key Performance Metrics -->
    <div class="grid-stats">
        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Total Users</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['users']['total'] ?? 0) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="absolute top-0 right-0 -mt-2 -mr-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                        +{{ $stats['users']['this_month'] ?? 0 }} this month
                    </span>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Premium Users</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['users']['premium'] ?? 0) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="absolute top-0 right-0 -mt-2 -mr-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800">
                        {{ round((($stats['users']['premium'] ?? 0) / max($stats['users']['total'], 1)) * 100, 1) }}%
                    </span>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Status Generated</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['content']['status_generated'] ?? 0) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="absolute top-0 right-0 -mt-2 -mr-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-800">
                        {{ $stats['content']['status_today'] ?? 0 }} today
                    </span>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Avg Rating</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['engagement']['avg_rating'] ?? 0, 1) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="absolute top-0 right-0 -mt-2 -mr-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800">
                        {{ $stats['engagement']['total_feedback'] ?? 0 }} reviews
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Growth Chart -->
        <div class="premium-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-slate-900">User Growth</h3>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm bg-primary-100 text-primary-700 rounded-lg">30 Days</button>
                    <button class="px-3 py-1 text-sm text-slate-500 hover:text-slate-700 rounded-lg">90 Days</button>
                </div>
            </div>
            <div class="h-64">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        <!-- Status Generation Chart -->
        <div class="premium-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-slate-900">Status Generation</h3>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm bg-info-100 text-info-700 rounded-lg">7 Days</button>
                    <button class="px-3 py-1 text-sm text-slate-500 hover:text-slate-700 rounded-lg">30 Days</button>
                </div>
            </div>
            <div class="h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Themes -->
        <div class="premium-card p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Popular Themes</h3>
            <div class="space-y-4">
                @forelse($chartData['top_themes'] ?? [] as $theme)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-primary-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-slate-700">{{ $theme['name'] }}</span>
                    </div>
                    <span class="text-sm text-slate-500">{{ $theme['count'] }}</span>
                </div>
                @empty
                <p class="text-sm text-slate-500">No data available</p>
                @endforelse
            </div>
        </div>

        <!-- User Activity -->
        <div class="premium-card p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">User Activity</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-700">Active Users</span>
                    <span class="text-sm font-medium text-slate-900">{{ number_format($stats['users']['active'] ?? 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-700">New This Week</span>
                    <span class="text-sm font-medium text-slate-900">{{ number_format($stats['users']['this_week'] ?? 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-700">New Today</span>
                    <span class="text-sm font-medium text-slate-900">{{ number_format($stats['users']['today'] ?? 0) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-700">Premium Rate</span>
                    <span class="text-sm font-medium text-slate-900">{{ round((($stats['users']['premium'] ?? 0) / max($stats['users']['total'], 1)) * 100, 1) }}%</span>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="premium-card p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">System Health</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-700">Database</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800">
                        Healthy
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-700">Cache</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-success-100 text-success-800">
                        {{ $stats['system']['cache_size'] ?? 0 }} keys
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-700">Queue</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-info-100 text-info-800">
                        {{ $stats['system']['queue_pending'] ?? 0 }} pending
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-700">Memory</span>
                    <span class="text-sm font-medium text-slate-900">{{ number_format((($stats['system']['memory_usage']['current'] ?? 0) / 1024 / 1024), 1) }}MB</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Export -->
    <div class="premium-card p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Export Data</h3>
                <p class="text-sm text-slate-600">Download analytics data for external analysis</p>
            </div>
            <div class="flex space-x-3">
                <button class="btn-secondary-premium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </button>
                <button class="btn-primary-premium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Generate Report
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: @json(collect($chartData['user_growth'] ?? [])->pluck('date')),
            datasets: [{
                label: 'New Users',
                data: @json(collect($chartData['user_growth'] ?? [])->pluck('users')),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Status Generation Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: @json(collect($chartData['status_generation'] ?? [])->pluck('date')),
            datasets: [{
                label: 'Status Generated',
                data: @json(collect($chartData['status_generation'] ?? [])->pluck('count')),
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endpush