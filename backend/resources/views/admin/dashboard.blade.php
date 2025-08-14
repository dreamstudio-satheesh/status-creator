@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Users -->
        <div class="admin-stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-admin-500 truncate">Total Users</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-admin-900">{{ number_format($stats['users']['total']) }}</div>
                            <div class="ml-2 flex items-baseline text-sm font-semibold text-success-600">
                                <svg class="self-center flex-shrink-0 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="sr-only">Increased by</span>
                                +{{ $stats['users']['today'] }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xs text-admin-500">
                    Active: {{ number_format($stats['users']['active']) }} | 
                    Premium: {{ number_format($stats['users']['premium']) }}
                </div>
            </div>
        </div>

        <!-- Status Generated -->
        <div class="admin-stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-success-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-admin-500 truncate">Status Generated</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-admin-900">{{ number_format($stats['content']['status_generated']) }}</div>
                            <div class="ml-2 flex items-baseline text-sm font-semibold text-success-600">
                                +{{ $stats['content']['status_today'] }} today
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="admin-stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-warning-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-admin-500 truncate">Monthly Revenue</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-admin-900">
                                ${{ number_format($stats['users']['premium'] * 9.99, 2) }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xs text-admin-500">
                    From {{ number_format($stats['users']['premium']) }} premium users
                </div>
            </div>
        </div>

        <!-- Feedback Score -->
        <div class="admin-stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-info-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-admin-500 truncate">Avg Rating</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-admin-900">{{ number_format($stats['engagement']['avg_rating'], 1) }}</div>
                            <div class="ml-2 text-sm text-admin-500">/5.0</div>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xs text-admin-500">
                    {{ number_format($stats['engagement']['total_feedback']) }} total reviews
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Growth Chart -->
        <div class="admin-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-admin-900">User Growth (30 Days)</h3>
                <div class="text-sm text-admin-500">{{ $stats['users']['this_month'] }} this month</div>
            </div>
            <div class="chart-container">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        <!-- Status Generation Chart -->
        <div class="admin-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-admin-900">Status Generation (7 Days)</h3>
                <div class="text-sm text-admin-500">{{ $stats['users']['this_week'] }} this week</div>
            </div>
            <div class="chart-container">
                <canvas id="statusGenerationChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Revenue and Top Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Chart -->
        <div class="admin-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-admin-900">Revenue Trend (6 Months)</h3>
                <div class="text-sm text-success-600 font-medium">
                    ${{ number_format(array_sum(array_column($chartData['revenue'], 'revenue')), 2) }} total
                </div>
            </div>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Top Themes -->
        <div class="admin-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-admin-900">Popular Themes</h3>
                <div class="text-sm text-admin-500">{{ $stats['content']['themes'] }} total themes</div>
            </div>
            <div class="space-y-3">
                @foreach($chartData['top_themes'] as $index => $theme)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full mr-3" style="background-color: {{ ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'][$index] ?? '#6B7280' }}"></div>
                        <span class="text-sm font-medium text-admin-900">{{ $theme['name'] }}</span>
                    </div>
                    <span class="text-sm text-admin-500">{{ number_format($theme['count']) }} uses</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- System Health and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Health -->
        <div class="admin-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-admin-900">System Health</h3>
                <button onclick="refreshSystemHealth()" class="text-sm text-primary-600 hover:text-primary-700">
                    Refresh
                </button>
            </div>
            <div id="system-health" class="space-y-3">
                <!-- System health items will be loaded via JavaScript -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-success-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-admin-900">Database</span>
                    </div>
                    <span class="admin-badge-success">Healthy</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-warning-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-admin-900">Disk Usage</span>
                    </div>
                    <span class="admin-badge-warning">{{ $stats['system']['disk_usage']['percentage'] }}%</span>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-success-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-admin-900">Queue</span>
                    </div>
                    <span class="admin-badge-info">{{ $stats['system']['queue_pending'] }} pending</span>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-success-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-admin-900">Cache</span>
                    </div>
                    <span class="admin-badge-success">{{ number_format($stats['system']['cache_size']) }} keys</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="admin-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-admin-900">Recent Activity</h3>
                <a href="#" class="text-sm text-primary-600 hover:text-primary-700">View all</a>
            </div>
            <div class="space-y-4">
                @php 
                $allActivity = collect($recentActivity['new_users'])
                    ->merge($recentActivity['recent_feedback'])
                    ->merge($recentActivity['status_generated'])
                    ->sortByDesc('time')
                    ->take(5);
                @endphp
                
                @foreach($allActivity as $activity)
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        @if($activity['type'] === 'user_registration')
                        <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        @elseif($activity['type'] === 'feedback')
                        <div class="w-8 h-8 bg-warning-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        @else
                        <div class="w-8 h-8 bg-success-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-admin-900">{{ $activity['message'] }}</p>
                        <p class="text-xs text-admin-500">{{ $activity['time']->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart configuration
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f1f5f9'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    };

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($chartData['user_growth'], 'date')) !!},
            datasets: [{
                label: 'New Users',
                data: {!! json_encode(array_column($chartData['user_growth'], 'users')) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: chartOptions
    });

    // Status Generation Chart
    const statusCtx = document.getElementById('statusGenerationChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($chartData['status_generation'], 'date')) !!},
            datasets: [{
                label: 'Status Generated',
                data: {!! json_encode(array_column($chartData['status_generation'], 'count')) !!},
                backgroundColor: '#10B981',
                borderColor: '#059669',
                borderWidth: 1
            }]
        },
        options: chartOptions
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($chartData['revenue'], 'month')) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode(array_column($chartData['revenue'], 'revenue')) !!},
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                ...chartOptions.scales,
                y: {
                    ...chartOptions.scales.y,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});

// System Health Refresh
function refreshSystemHealth() {
    fetch('{{ route("admin.system-health") }}')
        .then(response => response.json())
        .then(data => {
            // Update system health display
            console.log('System health updated:', data);
            window.showNotification('System health refreshed', 'success');
        })
        .catch(error => {
            console.error('Error refreshing system health:', error);
            window.showNotification('Failed to refresh system health', 'error');
        });
}

// Auto-refresh system health every 5 minutes
setInterval(refreshSystemHealth, 300000);
</script>
@endpush