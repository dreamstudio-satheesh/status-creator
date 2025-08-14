@extends('admin.layouts.app')

@section('title', $template->title . ' - Template Details')
@section('page_title', $template->title)
@section('page_subtitle', 'Template details and usage statistics')

@section('content')
<div class="space-responsive">
    <!-- Template Header -->
    <div class="premium-card">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 p-6">
            <!-- Template Preview -->
            <div class="flex-shrink-0">
                <div class="w-64 h-64 rounded-xl shadow-lg overflow-hidden relative" 
                     style="background: {{ $template->background_color ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }};">
                    @if($template->background_image)
                        <img src="{{ Storage::url($template->background_image) }}" 
                             alt="{{ $template->title }}" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                    @endif
                    
                    <div class="absolute inset-0 flex items-center justify-center p-6">
                        <div class="text-center space-y-3" style="color: {{ $template->text_color ?? '#ffffff' }}; text-align: {{ $template->text_alignment ?? 'center' }};">
                            @if($template->quote_text_ta)
                                <p class="font-medium leading-relaxed text-lg">{{ $template->quote_text_ta }}</p>
                            @elseif($template->quote_text)
                                <p class="font-medium leading-relaxed text-lg">{{ $template->quote_text }}</p>
                            @endif
                            
                            @if($template->author)
                                <p class="text-sm opacity-75">- {{ $template->author }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Info -->
            <div class="flex-1 space-y-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $template->title }}</h1>
                    @if($template->theme)
                        <div class="flex items-center mt-2">
                            @if($template->theme->icon)
                                <span class="mr-2 text-lg">{{ $template->theme->icon }}</span>
                            @endif
                            <span class="text-slate-600">{{ $template->theme->name }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    @if($template->is_active)
                        <span class="badge-success-glass">Active</span>
                    @else
                        <span class="badge-danger-glass">Inactive</span>
                    @endif
                    
                    @if($template->is_featured)
                        <span class="badge-info-glass">Featured</span>
                    @endif
                    
                    @if($template->is_premium)
                        <span class="badge-warning-glass">Premium</span>
                    @endif
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-slate-500">Font Family:</span>
                        <div class="font-medium">{{ $template->font_family ?? 'Default' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-500">Font Size:</span>
                        <div class="font-medium">{{ ucfirst($template->font_size ?? 'medium') }}</div>
                    </div>
                    <div>
                        <span class="text-slate-500">Text Color:</span>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full border mr-2" style="background-color: {{ $template->text_color ?? '#ffffff' }};"></div>
                            <span class="font-medium">{{ $template->text_color ?? '#ffffff' }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-500">Background:</span>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full border mr-2" style="background-color: {{ $template->background_color ?? '#3b82f6' }};"></div>
                            <span class="font-medium">{{ $template->background_color ?? '#3b82f6' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-3 pt-4">
                    <a href="{{ route('admin.templates.edit', $template) }}" class="btn-primary-premium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Template
                    </a>
                    
                    <a href="{{ route('admin.templates.create', ['clone' => $template->id]) }}" class="btn-secondary-premium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Clone Template
                    </a>
                    
                    <form method="POST" action="{{ route('admin.templates.destroy', $template) }}" 
                          onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.')" 
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary-premium text-danger-600 hover:text-danger-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid-stats">
        <div class="stats-card-premium">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-slate-500 truncate">Total Usage</dt>
                        <dd class="text-2xl font-bold text-slate-900">{{ $stats['total_usage'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-slate-500 truncate">This Month</dt>
                        <dd class="text-2xl font-bold text-slate-900">{{ $stats['monthly_usage'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-slate-500 truncate">Unique Users</dt>
                        <dd class="text-2xl font-bold text-slate-900">{{ $stats['unique_users'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-slate-500 truncate">Avg. Rating</dt>
                        <dd class="text-2xl font-bold text-slate-900">{{ number_format($stats['average_rating'], 1) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quote Content -->
        <div class="premium-card">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Quote Content</h3>
            </div>
            <div class="p-6 space-y-4">
                @if($template->quote_text)
                    <div>
                        <label class="text-sm font-medium text-slate-500">English Text:</label>
                        <div class="mt-1 p-3 bg-slate-50 rounded-lg">
                            <p class="text-slate-900">{{ $template->quote_text }}</p>
                        </div>
                    </div>
                @endif

                @if($template->quote_text_ta)
                    <div>
                        <label class="text-sm font-medium text-slate-500">Tamil Text:</label>
                        <div class="mt-1 p-3 bg-slate-50 rounded-lg">
                            <p class="text-slate-900" style="font-family: 'Tamil', serif;">{{ $template->quote_text_ta }}</p>
                        </div>
                    </div>
                @endif

                @if($template->author)
                    <div>
                        <label class="text-sm font-medium text-slate-500">Author:</label>
                        <div class="mt-1 p-3 bg-slate-50 rounded-lg">
                            <p class="text-slate-900">{{ $template->author }}</p>
                        </div>
                    </div>
                @endif

                @if(!$template->quote_text && !$template->quote_text_ta && !$template->author)
                    <div class="text-center py-8 text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        No quote content available
                    </div>
                @endif
            </div>
        </div>

        <!-- Template Metadata -->
        <div class="premium-card">
            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Template Details</h3>
            </div>
            <div class="p-6">
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Created</dt>
                        <dd class="text-slate-900">{{ $template->created_at->format('M j, Y \\a\\t g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Last Updated</dt>
                        <dd class="text-slate-900">{{ $template->updated_at->format('M j, Y \\a\\t g:i A') }}</dd>
                    </div>
                    @if($template->background_image)
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Background Image</dt>
                            <dd class="mt-1">
                                <a href="{{ Storage::url($template->background_image) }}" 
                                   target="_blank" 
                                   class="text-primary-600 hover:text-primary-900">
                                    View Image
                                </a>
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Generation Method</dt>
                        <dd class="text-slate-900">
                            @if($template->generated_by_ai)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                    </svg>
                                    AI Generated
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Manual Creation
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Recent Usage -->
    @if($recentUsage->count() > 0)
    <div class="premium-card">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900">Recent Usage</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($recentUsage as $usage)
                    <tr class="hover:bg-slate-50">
                        <td>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-primary-600">
                                            {{ substr($usage->user->name ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ $usage->user->name ?? 'Anonymous User' }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ $usage->user->email ?? 'No email' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-slate-500">
                            {{ $usage->created_at->format('M j, Y g:i A') }}
                        </td>
                        <td>
                            @if($usage->status === 'completed')
                                <span class="badge-success-glass">Completed</span>
                            @elseif($usage->status === 'processing')
                                <span class="badge-warning-glass">Processing</span>
                            @else
                                <span class="badge-danger-glass">Failed</span>
                            @endif
                        </td>
                        <td>
                            @if($usage->image_path)
                                <a href="{{ Storage::url($usage->image_path) }}" 
                                   target="_blank" 
                                   class="text-primary-600 hover:text-primary-900 text-sm">
                                    View Image
                                </a>
                            @else
                                <span class="text-slate-400 text-sm">No image</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection