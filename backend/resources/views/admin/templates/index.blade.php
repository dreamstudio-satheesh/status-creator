@extends('admin.layouts.app')

@section('title', 'Templates Management')
@section('page_title', 'Templates')
@section('page_subtitle', 'Manage status templates and content')

@section('content')
<div class="space-responsive">
    <!-- Stats Cards -->
    <div class="grid-stats">
        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Total Templates</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ $stats['total'] }}</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Active</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ $stats['active'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Premium</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ $stats['premium'] }}</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">With Usage</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ $stats['with_user_creations'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="premium-card p-4">
        <div class="flex items-center gap-4">
            <form method="GET" class="flex items-center gap-2">
                <div style="width: 22rem;">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search templates..." 
                           class="input-premium w-full">
                </div>
                <select name="theme_id" class="input-premium w-32">
                    <option value="">All Themes</option>
                    @foreach($themes as $theme)
                    <option value="{{ $theme->id }}" {{ request('theme_id') == $theme->id ? 'selected' : '' }}>
                        {{ $theme->name }}
                    </option>
                    @endforeach
                </select>
                <select name="status" class="input-premium w-32">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn-primary-premium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Search
                </button>
            </form>
            <a href="{{ route('admin.templates.create') }}" class="btn-primary-premium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Template
            </a>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Theme</th>
                        <th>Quote</th>
                        <th>Status</th>
                        <th>Usage</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($templates as $template)
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
                            @if($template->theme)
                            <div class="flex items-center">
                                @if($template->theme->icon)
                                <span class="mr-2">{{ $template->theme->icon }}</span>
                                @endif
                                <span class="text-sm font-medium text-slate-900">{{ $template->theme->name }}</span>
                            </div>
                            @else
                            <span class="text-slate-400">No theme</span>
                            @endif
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
                            <div class="flex flex-wrap gap-1">
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
                                <form method="POST" action="{{ route('admin.templates.destroy', $template) }}" 
                                      onsubmit="return confirm('Are you sure you want to delete this template?')" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger-600 hover:text-danger-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-500">
                            <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            No templates found. <a href="{{ route('admin.templates.create') }}" class="text-primary-600 hover:text-primary-900">Create your first template</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
        <div class="bg-slate-50 px-4 py-3 border-t border-slate-200">
            {{ $templates->links() }}
        </div>
        @endif
    </div>
</div>
@endsection