@extends('admin.layouts.app')

@section('title', 'AI Management')
@section('page_title', 'AI Management')
@section('page_subtitle', 'Monitor and manage AI services performance')

@section('content')
<div class="space-responsive">
    <!-- Stats Cards -->
    <div class="grid-stats">
        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Total Generations</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['total_generations']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Today's Generations</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['today_generations']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Total Cost</dt>
                            <dd class="text-3xl font-bold text-slate-900">${{ number_format($stats['total_cost'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Avg Response Time</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['avg_response_time'] ?? 0) }}ms</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Usage Chart -->
        <div class="premium-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-slate-900">Usage Over Time</h3>
                <div class="flex space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                        Last 30 Days
                    </span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="usageChart"></canvas>
            </div>
        </div>

        <!-- Model Stats -->
        <div class="premium-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-slate-900">Model Performance</h3>
            </div>
            <div class="space-y-4">
                @forelse($modelStats as $model)
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-slate-900">{{ $model->model_used ?? 'Unknown Model' }}</h4>
                        <p class="text-xs text-slate-500">{{ number_format($model->count) }} generations</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-slate-900">${{ number_format($model->total_cost, 3) }}</div>
                        <div class="text-xs text-slate-500">{{ number_format($model->avg_time ?? 0) }}ms avg</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-500">
                    <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    No model data available yet.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Logs -->
    <div class="premium-card overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Recent AI Generations</h3>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.ai.usage') }}" class="btn-secondary-premium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        View Usage Details
                    </a>
                    <a href="{{ route('admin.ai.costs') }}" class="btn-secondary-premium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        Cost Analysis
                    </a>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Model</th>
                        <th>Status</th>
                        <th>Cost</th>
                        <th>Response Time</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-slate-50">
                        <td>
                            <div class="flex items-center">
                                @if($log->user)
                                <div class="flex-shrink-0 h-8 w-8">
                                    @if($log->user->avatar)
                                        <img class="h-8 w-8 rounded-full" src="{{ $log->user->avatar }}" alt="">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-600 font-medium text-xs">
                                                {{ substr($log->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-slate-900">{{ $log->user->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $log->user->email }}</div>
                                </div>
                                @else
                                <div class="text-sm text-slate-500">System</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="text-sm font-medium text-slate-900">{{ $log->model_used ?? 'Unknown' }}</div>
                            <div class="text-xs text-slate-500">{{ $log->provider ?? 'N/A' }}</div>
                        </td>
                        <td>
                            @if($log->status === 'success')
                                <span class="badge-success-glass">Success</span>
                            @else
                                <span class="badge-danger-glass">Failed</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-sm font-medium text-slate-900">${{ number_format($log->cost ?? 0, 4) }}</div>
                            @if($log->tokens_used)
                                <div class="text-xs text-slate-500">{{ number_format($log->tokens_used) }} tokens</div>
                            @endif
                        </td>
                        <td>
                            <div class="text-sm text-slate-900">{{ number_format($log->response_time_ms ?? 0) }}ms</div>
                        </td>
                        <td>
                            <div class="text-sm text-slate-900">{{ $log->created_at->format('M j, H:i') }}</div>
                            <div class="text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <button type="button" onclick="showLogDetails({{ $log->id }})" 
                                        class="text-primary-600 hover:text-primary-900">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                @if($log->error_message)
                                <button type="button" onclick="showError('{{ addslashes($log->error_message) }}')" 
                                        class="text-danger-600 hover:text-danger-900">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-500">
                            <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            No AI generation logs found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div id="logDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Generation Details</h3>
                    <div id="logDetailsContent" class="mt-2">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="closeModal()" class="btn-secondary-premium">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Usage Chart
    const usageCtx = document.getElementById('usageChart').getContext('2d');
    const usageData = @json($usageChart);
    
    new Chart(usageCtx, {
        type: 'line',
        data: {
            labels: usageData.map(item => item.date),
            datasets: [{
                label: 'Generations',
                data: usageData.map(item => item.count),
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Cost ($)',
                data: usageData.map(item => item.cost),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
});

function showLogDetails(logId) {
    // This would typically make an AJAX call to get log details
    document.getElementById('logDetailsModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('logDetailsModal').classList.add('hidden');
}

function showError(message) {
    alert('Error: ' + message);
}
</script>
@endpush