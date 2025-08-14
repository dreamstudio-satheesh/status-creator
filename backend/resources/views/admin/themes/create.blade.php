@extends('admin.layouts.app')

@section('title', 'Create Theme')
@section('page_title', 'Create Theme')
@section('page_subtitle', 'Add a new theme for status templates')

@section('content')
<div class="max-w-2xl mx-auto space-responsive">
    <div class="premium-card">
        <form method="POST" action="{{ route('admin.themes.store') }}" class="padding-responsive">
            @csrf
            
            <div class="form-grid">
                <!-- Name -->
                <div>
                    <label for="name" class="label-premium">Theme Name (English)</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" 
                           class="input-premium @error('name') border-danger-300 focus:ring-danger-500 @enderror" 
                           placeholder="e.g., Love & Romance">
                    @error('name')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tamil Name -->
                <div>
                    <label for="name_ta" class="label-premium">Theme Name (Tamil)</label>
                    <input type="text" id="name_ta" name="name_ta" value="{{ old('name_ta') }}" 
                           class="input-premium @error('name_ta') border-danger-300 focus:ring-danger-500 @enderror" 
                           placeholder="e.g., காதல் & காமமுது">
                    @error('name_ta')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="form-grid-full">
                    <label for="description" class="label-premium">Description</label>
                    <textarea id="description" name="description" rows="3" 
                              class="input-premium @error('description') border-danger-300 focus:ring-danger-500 @enderror" 
                              placeholder="Describe this theme and what kind of content it includes">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Icon -->
                <div>
                    <label for="icon" class="label-premium">Icon</label>
                    <input type="text" id="icon" name="icon" value="{{ old('icon') }}" 
                           class="input-premium @error('icon') border-danger-300 focus:ring-danger-500 @enderror" 
                           placeholder="❤️">
                    <p class="mt-1 text-xs text-slate-500">Use an emoji or icon character</p>
                    @error('icon')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Color -->
                <div>
                    <label for="color" class="label-premium">Color</label>
                    <input type="color" id="color" name="color" value="{{ old('color', '#3b82f6') }}" 
                           class="input-premium h-10 @error('color') border-danger-300 focus:ring-danger-500 @enderror">
                    @error('color')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Order Index -->
                <div>
                    <label for="order_index" class="label-premium">Sort Order</label>
                    <input type="number" id="order_index" name="order_index" value="{{ old('order_index', 0) }}" 
                           class="input-premium @error('order_index') border-danger-300 focus:ring-danger-500 @enderror" 
                           min="0" max="999">
                    <p class="mt-1 text-xs text-slate-500">Lower numbers appear first</p>
                    @error('order_index')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="form-grid-full">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm font-medium text-slate-700">
                            Active (visible to users)
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-slate-200">
                <a href="{{ route('admin.themes.index') }}" class="btn-secondary-premium">
                    Cancel
                </a>
                <button type="submit" class="btn-primary-premium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Theme
                </button>
            </div>
        </form>
    </div>
</div>
@endsection