@extends('admin.layouts.app')

@section('title', 'User Feedback')
@section('page_title', 'Feedback')
@section('page_subtitle', 'Manage user feedback and support requests')

@section('content')
<div class="space-responsive">
    <!-- Stats Cards -->
    <div class="grid-stats">
        <div class="stats-card-premium">
            <div class="relative">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Total Feedback</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['total']) }}</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Unread</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['unread']) }}</dd>
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
                            <dt class="text-sm font-medium text-slate-500 truncate">Resolved</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['resolved']) }}</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-slate-500 truncate">Avg Rating</dt>
                            <dd class="text-3xl font-bold text-slate-900">{{ number_format($stats['avg_rating'], 1) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="premium-card p-4">
        <form method="GET" action="{{ route('admin.feedback.index') }}" class="flex items-center gap-2">
            <div class="w-80">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by user, subject, or message..."
                       class="input-premium w-full">
            </div>
            <select name="rating" class="input-premium w-32">
                <option value="">All Ratings</option>
                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐</option>
                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐</option>
                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>⭐⭐⭐</option>
                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>⭐⭐</option>
                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>⭐</option>
            </select>
            <select name="status" class="input-premium w-32">
                <option value="">All Status</option>
                <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="input-premium w-32">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="input-premium w-32">
            <button type="submit" class="btn-primary-premium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Search
            </button>
            <a href="{{ route('admin.feedback.index') }}" class="btn-secondary-premium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Clear
            </a>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="premium-card p-4">
        <form id="bulkActionForm" method="POST" action="{{ route('admin.feedback.bulk-action') }}">
            @csrf
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    <label for="selectAll" class="text-sm font-medium text-slate-700">Select All</label>
                    
                    <select name="action" class="input-premium w-40" required>
                        <option value="">Bulk Actions</option>
                        <option value="mark_read">Mark as Read</option>
                        <option value="mark_resolved">Mark as Resolved</option>
                        <option value="delete">Delete</option>
                    </select>
                    
                    <button type="submit" class="btn-secondary-premium" disabled id="bulkActionBtn">
                        Apply
                    </button>
                </div>
                
                <a href="{{ route('admin.feedback.export', request()->query()) }}" class="btn-secondary-premium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
            </div>
        </form>
    </div>

    <!-- Feedback Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($feedback as $item)
                    <tr class="hover:bg-slate-50 {{ $item->status === 'unread' ? 'bg-blue-50' : '' }}">
                        <td>
                            <input type="checkbox" name="feedback_ids[]" value="{{ $item->id }}" 
                                   class="feedback-checkbox rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        </td>
                        <td>
                            <div class="flex items-center">
                                @if($item->user)
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($item->user->avatar)
                                        <img class="h-10 w-10 rounded-full" src="{{ $item->user->avatar }}" alt="">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-600 font-medium text-sm">
                                                {{ substr($item->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-slate-900">{{ $item->user->name }}</div>
                                    <div class="text-sm text-slate-500">{{ $item->user->email }}</div>
                                </div>
                                @else
                                <div class="text-sm text-slate-500">Anonymous User</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="max-w-xs">
                                <div class="text-sm font-medium text-slate-900">{{ Str::limit($item->subject, 50) }}</div>
                                <div class="text-sm text-slate-500">{{ Str::limit($item->message, 80) }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $item->rating ? 'text-yellow-400' : 'text-slate-300' }}" 
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="ml-2 text-sm text-slate-600">({{ $item->rating }})</span>
                            </div>
                        </td>
                        <td>
                            @if($item->status === 'unread')
                                <span class="badge-warning-glass">Unread</span>
                            @elseif($item->status === 'read')
                                <span class="badge-info-glass">Read</span>
                            @else
                                <span class="badge-success-glass">Resolved</span>
                            @endif
                        </td>
                        <td class="text-sm text-slate-500">
                            {{ $item->created_at->format('M j, Y') }}
                            <div class="text-xs text-slate-400">{{ $item->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.feedback.show', $item) }}" 
                                   class="text-primary-600 hover:text-primary-900">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.feedback.destroy', $item) }}" 
                                      onsubmit="return confirm('Are you sure you want to delete this feedback?')" 
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            No feedback found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($feedback->hasPages())
        <div class="bg-slate-50 px-4 py-3 border-t border-slate-200">
            {{ $feedback->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const feedbackCheckboxes = document.querySelectorAll('.feedback-checkbox');
    const bulkActionForm = document.getElementById('bulkActionForm');
    const bulkActionBtn = document.getElementById('bulkActionBtn');
    
    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        feedbackCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionButton();
    });
    
    // Individual checkbox change
    feedbackCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.feedback-checkbox:checked').length;
            selectAllCheckbox.checked = checkedCount === feedbackCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < feedbackCheckboxes.length;
            updateBulkActionButton();
        });
    });
    
    function updateBulkActionButton() {
        const checkedCount = document.querySelectorAll('.feedback-checkbox:checked').length;
        bulkActionBtn.disabled = checkedCount === 0;
    }
    
    // Bulk action form submission
    bulkActionForm.addEventListener('submit', function(e) {
        const checkedCount = document.querySelectorAll('.feedback-checkbox:checked').length;
        const action = document.querySelector('select[name="action"]').value;
        
        if (checkedCount === 0) {
            e.preventDefault();
            alert('Please select at least one feedback item.');
            return;
        }
        
        if (!action) {
            e.preventDefault();
            alert('Please select an action.');
            return;
        }
        
        if (action === 'delete') {
            if (!confirm(`Are you sure you want to delete ${checkedCount} feedback item(s)?`)) {
                e.preventDefault();
            }
        }
    });
});
</script>
@endpush