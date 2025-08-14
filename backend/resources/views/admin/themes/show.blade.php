@extends('admin.layouts.app')

@section('title', $theme->name . ' - Theme Details')
@section('page_title', $theme->name)
@section('page_subtitle', 'Theme details and statistics')

@section('content')
<div class="space-responsive">
    <!-- Theme Header -->
    <div class="premium-card">
        <div class="flex items-center justify-between p-6">
            <div class="flex items-center space-x-4">
                @if($theme->icon)
                <div class="h-16 w-16 rounded-xl flex items-center justify-center text-2xl" 
                     style="background-color: {{ $theme->color ?? '#e2e8f0' }};">
                    {{ $theme->icon }}
                </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $theme->name }}</h1>
                    @if($theme->name_ta)
                    <p class="text-lg text-slate-600">{{ $theme->name_ta }}</p>
                    @endif
                    <p class="text-slate-500 mt-1">{{ $theme->description }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.themes.edit', $theme) }}" class="btn-primary-premium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Theme
                </a>
                <form method="POST" action="{{ route('admin.themes.destroy', $theme) }}" 
                      onsubmit="return confirm('Are you sure you want to delete this theme? This action cannot be undone.')" 
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

    <!-- Stats -->
    <div class="grid-stats">
        <div class="stats-card-premium">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-slate-500 truncate">Total Templates</dt>
                        <dd class="text-2xl font-bold text-slate-900">{{ $stats['total_templates'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-slate-500 truncate">Active Templates</dt>
                        <dd class="text-2xl font-bold text-slate-900">{{ $stats['active_templates'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

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
                        <dd class="text-2xl font-bold text-slate-900">{{ $stats['usage_count'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-slate-500 truncate">Recent Usage</dt>
                        <dd class="text-2xl font-bold text-slate-900">{{ $stats['recent_usage'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Theme Templates -->
    <div class="premium-card">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Templates</h3>
                <a href="{{ route('admin.templates.create', ['theme_id' => $theme->id]) }}" class="btn-primary-premium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Template
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Quote</th>
                        <th>Status</th>
                        <th>Usage</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($theme->templates as $template)
                    <tr class="hover:bg-slate-50">
                        <td>
                            <div class="flex items-center">
                                @if($template->background_image)
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-lg object-cover" 
                                         src="{{ Storage::url($template->background_image) }}" 
                                         alt="{{ $template->title }}">
                                </div>
                                @endif
                                <div class="ml-4">
                                    <div class="font-semibold text-slate-900">{{ $template->title }}</div>
                                    <div class="text-sm text-slate-500">{{ $template->font_family ?? 'Default' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="max-w-xs">
                                <div class="text-sm text-slate-900 truncate">{{ Str::limit($template->quote_text, 50) }}</div>
                                @if($template->quote_text_ta)
                                <div class="text-xs text-slate-500 truncate">{{ Str::limit($template->quote_text_ta, 30) }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
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
                        </td>
                        <td>
                            <span class="text-sm font-medium text-slate-900">{{ $template->user_creations_count ?? 0 }}</span>
                        </td>
                        <td class="text-sm text-slate-500">
                            {{ $template->created_at->format('M j, Y') }}
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.templates.show', $template) }}" 
                                   class="text-primary-600 hover:text-primary-900">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.templates.edit', $template) }}" 
                                   class="text-slate-600 hover:text-slate-900">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-500">
                            <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            No templates found for this theme.
                            <a href="{{ route('admin.templates.create', ['theme_id' => $theme->id]) }}" class="text-primary-600 hover:text-primary-900">Create the first template</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection