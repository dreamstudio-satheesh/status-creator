@extends('admin.layouts.app')

@section('title', 'Edit Template - ' . $template->title)
@section('page_title', 'Edit Template')
@section('page_subtitle', 'Modify template content and styling')

@section('content')
<div class="max-w-4xl mx-auto space-responsive">
    <div class="premium-card">
        <form method="POST" action="{{ route('admin.templates.update', $template) }}" enctype="multipart/form-data" class="padding-responsive">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3 class="section-title">Basic Information</h3>
                        
                        <div class="form-grid">
                            <!-- Theme -->
                            <div class="form-grid-full">
                                <label for="theme_id" class="label-premium">Theme</label>
                                <select id="theme_id" name="theme_id" 
                                        class="input-premium @error('theme_id') border-danger-300 focus:ring-danger-500 @enderror">
                                    <option value="">Select a theme</option>
                                    @foreach($themes as $theme)
                                    <option value="{{ $theme->id }}" {{ old('theme_id', $template->theme_id) == $theme->id ? 'selected' : '' }}>
                                        @if($theme->icon){{ $theme->icon }} @endif{{ $theme->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('theme_id')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Title -->
                            <div>
                                <label for="title" class="label-premium">Template Title</label>
                                <input type="text" id="title" name="title" value="{{ old('title', $template->title) }}" 
                                       class="input-premium @error('title') border-danger-300 focus:ring-danger-500 @enderror" 
                                       placeholder="e.g., Romantic Love Quote">
                                @error('title')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Font Family -->
                            <div>
                                <label for="font_family" class="label-premium">Font Family</label>
                                <select id="font_family" name="font_family" 
                                        class="input-premium @error('font_family') border-danger-300 focus:ring-danger-500 @enderror">
                                    <option value="">Default Font</option>
                                    <option value="Tamil" {{ old('font_family', $template->font_family) == 'Tamil' ? 'selected' : '' }}>Tamil</option>
                                    <option value="Roboto" {{ old('font_family', $template->font_family) == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                                    <option value="Poppins" {{ old('font_family', $template->font_family) == 'Poppins' ? 'selected' : '' }}>Poppins</option>
                                    <option value="Inter" {{ old('font_family', $template->font_family) == 'Inter' ? 'selected' : '' }}>Inter</option>
                                </select>
                                @error('font_family')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Quote Content -->
                    <div class="form-section">
                        <h3 class="section-title">Quote Content</h3>
                        
                        <div class="space-y-4">
                            <!-- Quote Text (English) -->
                            <div>
                                <label for="quote_text" class="label-premium">Quote Text (English)</label>
                                <textarea id="quote_text" name="quote_text" rows="4" 
                                          class="input-premium @error('quote_text') border-danger-300 focus:ring-danger-500 @enderror" 
                                          placeholder="Enter the quote text in English">{{ old('quote_text', $template->quote_text) }}</textarea>
                                @error('quote_text')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quote Text (Tamil) -->
                            <div>
                                <label for="quote_text_ta" class="label-premium">Quote Text (Tamil)</label>
                                <textarea id="quote_text_ta" name="quote_text_ta" rows="4" 
                                          class="input-premium @error('quote_text_ta') border-danger-300 focus:ring-danger-500 @enderror" 
                                          placeholder="தமிழில் மேற்கோள் உரையை உள்ளிடவும்">{{ old('quote_text_ta', $template->quote_text_ta) }}</textarea>
                                @error('quote_text_ta')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Author -->
                            <div>
                                <label for="author" class="label-premium">Author <span class="text-slate-400">(Optional)</span></label>
                                <input type="text" id="author" name="author" value="{{ old('author', $template->author) }}" 
                                       class="input-premium @error('author') border-danger-300 focus:ring-danger-500 @enderror" 
                                       placeholder="Quote author name">
                                @error('author')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Styling Options -->
                    <div class="form-section">
                        <h3 class="section-title">Styling Options</h3>
                        
                        <div class="form-grid">
                            <!-- Font Size -->
                            <div>
                                <label for="font_size" class="label-premium">Font Size</label>
                                <select id="font_size" name="font_size" 
                                        class="input-premium @error('font_size') border-danger-300 focus:ring-danger-500 @enderror">
                                    <option value="14" {{ old('font_size', $template->font_size) == '14' ? 'selected' : '' }}>Small (14px)</option>
                                    <option value="18" {{ old('font_size', $template->font_size ?? '18') == '18' ? 'selected' : '' }}>Medium (18px)</option>
                                    <option value="24" {{ old('font_size', $template->font_size) == '24' ? 'selected' : '' }}>Large (24px)</option>
                                    <option value="32" {{ old('font_size', $template->font_size) == '32' ? 'selected' : '' }}>Extra Large (32px)</option>
                                </select>
                                @error('font_size')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Text Color -->
                            <div>
                                <label for="text_color" class="label-premium">Text Color</label>
                                <input type="color" id="text_color" name="text_color" value="{{ old('text_color', $template->text_color ?? '#ffffff') }}" 
                                       class="input-premium h-10 @error('text_color') border-danger-300 focus:ring-danger-500 @enderror">
                                @error('text_color')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Background Color -->
                            <div>
                                <label for="background_color" class="label-premium">Background Color</label>
                                <input type="color" id="background_color" name="background_color" value="{{ old('background_color', $template->background_color ?? '#3b82f6') }}" 
                                       class="input-premium h-10 @error('background_color') border-danger-300 focus:ring-danger-500 @enderror">
                                @error('background_color')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Text Alignment -->
                            <div>
                                <label for="text_alignment" class="label-premium">Text Alignment</label>
                                <select id="text_alignment" name="text_alignment" 
                                        class="input-premium @error('text_alignment') border-danger-300 focus:ring-danger-500 @enderror">
                                    <option value="left" {{ old('text_alignment', $template->text_alignment) == 'left' ? 'selected' : '' }}>Left</option>
                                    <option value="center" {{ old('text_alignment', $template->text_alignment ?? 'center') == 'center' ? 'selected' : '' }}>Center</option>
                                    <option value="right" {{ old('text_alignment', $template->text_alignment) == 'right' ? 'selected' : '' }}>Right</option>
                                </select>
                                @error('text_alignment')
                                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Preview -->
                    <div class="form-section">
                        <h3 class="section-title">Preview</h3>
                        <div id="template-preview" class="aspect-square bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center text-white p-6">
                            @if($template->background_image)
                                <div class="w-full h-full rounded-lg overflow-hidden relative" 
                                     style="background-image: url('{{ Storage::url($template->background_image) }}'); background-size: cover; background-position: center;">
                            @endif
                            <div class="text-center space-y-2">
                                <p class="font-medium">Live Preview</p>
                                <p class="text-sm opacity-75">Your template will appear here</p>
                            </div>
                            @if($template->background_image)
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Background Image -->
                    <div class="form-section">
                        <h3 class="section-title">Background Image</h3>
                        
                        @if($template->background_image)
                            <div class="mb-4">
                                <label class="text-sm font-medium text-slate-500">Current Image:</label>
                                <div class="mt-1">
                                    <img src="{{ Storage::url($template->background_image) }}" 
                                         alt="Current background" 
                                         class="w-full h-32 object-cover rounded-lg border">
                                </div>
                            </div>
                        @endif
                        
                        <div>
                            <label for="background_image" class="label-premium">Upload New Image</label>
                            <input type="file" id="background_image" name="background_image" 
                                   accept="image/*"
                                   class="input-premium @error('background_image') border-danger-300 focus:ring-danger-500 @enderror">
                            <p class="mt-1 text-xs text-slate-500">Leave empty to keep current image. Recommended: 1080x1080px, JPG or PNG</p>
                            @error('background_image')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($template->background_image)
                            <div class="mt-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="remove_background_image" value="1" 
                                           class="h-4 w-4 text-danger-600 focus:ring-danger-500 border-slate-300 rounded">
                                    <span class="ml-2 text-sm text-slate-700">Remove current background image</span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <!-- Settings -->
                    <div class="form-section">
                        <h3 class="section-title">Settings</h3>
                        
                        <div class="space-y-4">
                            <!-- Status Toggles -->
                            <div class="flex items-center">
                                <input type="checkbox" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm font-medium text-slate-700">
                                    Active (visible to users)
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                                       {{ old('is_featured', $template->is_featured) ? 'checked' : '' }}
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded">
                                <label for="is_featured" class="ml-2 block text-sm font-medium text-slate-700">
                                    Featured template
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="is_premium" name="is_premium" value="1" 
                                       {{ old('is_premium', $template->is_premium) ? 'checked' : '' }}
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded">
                                <label for="is_premium" class="ml-2 block text-sm font-medium text-slate-700">
                                    Premium template
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Template Info -->
                    <div class="p-4 bg-slate-50 rounded-lg">
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-slate-600">Created:</span>
                                <span class="font-medium text-slate-900">{{ $template->created_at->format('M j, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-slate-600">Usage Count:</span>
                                <span class="font-medium text-slate-900">{{ $template->user_creations_count ?? 0 }}</span>
                            </div>
                            @if($template->generated_by_ai)
                                <div>
                                    <span class="text-slate-600">Method:</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        AI Generated
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between mt-8 pt-6 border-t border-slate-200">
                <div>
                    <a href="{{ route('admin.templates.create', ['clone' => $template->id]) }}" class="btn-secondary-premium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Clone Template
                    </a>
                </div>
                
                <div class="flex space-x-3">
                    <a href="{{ route('admin.templates.show', $template) }}" class="btn-secondary-premium">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary-premium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Template
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live preview functionality
    const previewElement = document.getElementById('template-preview');
    const quoteTextEn = document.getElementById('quote_text');
    const quoteTextTa = document.getElementById('quote_text_ta');
    const textColor = document.getElementById('text_color');
    const backgroundColor = document.getElementById('background_color');
    const textAlignment = document.getElementById('text_alignment');
    const fontSize = document.getElementById('font_size');
    const author = document.getElementById('author');
    const backgroundImage = document.getElementById('background_image');
    const removeBackgroundImage = document.querySelector('input[name="remove_background_image"]');

    function updatePreview() {
        const quote = quoteTextTa.value || quoteTextEn.value || 'Your quote will appear here';
        const authorText = author.value ? `- ${author.value}` : '';
        const color = textColor.value || '#ffffff';
        const bgColor = backgroundColor.value || '#3b82f6';
        const alignment = textAlignment.value || 'center';
        const size = fontSize.value || '18';
        
        let sizeClass = 'text-base';
        const sizeValue = parseInt(size);
        if (sizeValue <= 14) {
            sizeClass = 'text-sm';
        } else if (sizeValue <= 18) {
            sizeClass = 'text-base';
        } else if (sizeValue <= 24) {
            sizeClass = 'text-lg';
        } else {
            sizeClass = 'text-xl';
        }
        
        previewElement.style.color = color;
        previewElement.style.backgroundColor = bgColor;
        previewElement.style.textAlign = alignment;
        
        previewElement.innerHTML = `
            <div class="space-y-2">
                <p class="${sizeClass} font-medium leading-relaxed">${quote}</p>
                ${authorText ? `<p class="text-sm opacity-75">${authorText}</p>` : ''}
            </div>
        `;
    }

    // Background image preview
    backgroundImage.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewElement.style.backgroundImage = `url(${e.target.result})`;
                previewElement.style.backgroundSize = 'cover';
                previewElement.style.backgroundPosition = 'center';
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove background image
    if (removeBackgroundImage) {
        removeBackgroundImage.addEventListener('change', function() {
            if (this.checked) {
                previewElement.style.backgroundImage = 'none';
            } else {
                // Reset to current background if unchecked
                @if($template->background_image)
                previewElement.style.backgroundImage = `url('{{ Storage::url($template->background_image) }}')`;
                @endif
            }
        });
    }

    // Bind events for live preview
    [quoteTextEn, quoteTextTa, textColor, backgroundColor, textAlignment, fontSize, author].forEach(element => {
        element.addEventListener('input', updatePreview);
        element.addEventListener('change', updatePreview);
    });

    // Initial preview
    updatePreview();
    
    // Set initial background image if exists
    @if($template->background_image)
    previewElement.style.backgroundImage = `url('{{ Storage::url($template->background_image) }}')`;
    previewElement.style.backgroundSize = 'cover';
    previewElement.style.backgroundPosition = 'center';
    @endif
});
</script>
@endsection