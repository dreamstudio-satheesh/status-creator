<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThemeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = Theme::withCount(['templates', 'statusHistories']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by premium status
        if ($request->has('premium')) {
            if ($request->premium === 'free') {
                $query->where('is_premium', false);
            } elseif ($request->premium === 'premium') {
                $query->where('is_premium', true);
            }
        }

        $themes = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Theme::count(),
            'active' => Theme::where('is_active', true)->count(),
            'premium' => Theme::where('is_premium', true)->count(),
            'categories' => Theme::distinct('category')->count('category'),
        ];

        $categories = Theme::distinct('category')->pluck('category')->filter();

        return view('admin.themes.index', compact('themes', 'stats', 'categories'));
    }

    public function show(Theme $theme)
    {
        $theme->load(['templates' => function($q) {
            $q->withCount('statusHistories')->orderBy('created_at', 'desc');
        }]);

        $stats = [
            'total_templates' => $theme->templates()->count(),
            'active_templates' => $theme->templates()->where('is_active', true)->count(),
            'usage_count' => $theme->statusHistories()->count(),
            'recent_usage' => $theme->statusHistories()->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.themes.show', compact('theme', 'stats'));
    }

    public function create()
    {
        $categories = Theme::distinct('category')->pluck('category')->filter();
        return view('admin.themes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:themes,name',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'color_scheme' => 'required|array',
            'font_family' => 'nullable|string|max:100',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'preview_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $themeData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'category' => $request->category,
            'color_scheme' => $request->color_scheme,
            'font_family' => $request->font_family,
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ];

        // Handle file uploads
        if ($request->hasFile('background_image')) {
            $themeData['background_image'] = $request->file('background_image')
                ->store('themes/backgrounds', 'public');
        }

        if ($request->hasFile('preview_image')) {
            $themeData['preview_image'] = $request->file('preview_image')
                ->store('themes/previews', 'public');
        }

        $theme = Theme::create($themeData);

        return redirect()->route('admin.themes.show', $theme)
            ->with('success', 'Theme created successfully');
    }

    public function edit(Theme $theme)
    {
        $categories = Theme::distinct('category')->pluck('category')->filter();
        return view('admin.themes.edit', compact('theme', 'categories'));
    }

    public function update(Request $request, Theme $theme)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:themes,name,' . $theme->id,
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'color_scheme' => 'required|array',
            'font_family' => 'nullable|string|max:100',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'preview_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $themeData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'category' => $request->category,
            'color_scheme' => $request->color_scheme,
            'font_family' => $request->font_family,
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ];

        // Handle file uploads
        if ($request->hasFile('background_image')) {
            // Delete old image
            if ($theme->background_image) {
                Storage::disk('public')->delete($theme->background_image);
            }
            $themeData['background_image'] = $request->file('background_image')
                ->store('themes/backgrounds', 'public');
        }

        if ($request->hasFile('preview_image')) {
            // Delete old image
            if ($theme->preview_image) {
                Storage::disk('public')->delete($theme->preview_image);
            }
            $themeData['preview_image'] = $request->file('preview_image')
                ->store('themes/previews', 'public');
        }

        $theme->update($themeData);

        return redirect()->route('admin.themes.show', $theme)
            ->with('success', 'Theme updated successfully');
    }

    public function destroy(Theme $theme)
    {
        // Check if theme has associated templates
        if ($theme->templates()->count() > 0) {
            return redirect()->route('admin.themes.index')
                ->with('error', 'Cannot delete theme with associated templates. Please remove templates first.');
        }

        // Delete associated files
        if ($theme->background_image) {
            Storage::disk('public')->delete($theme->background_image);
        }
        if ($theme->preview_image) {
            Storage::disk('public')->delete($theme->preview_image);
        }

        $theme->delete();

        return redirect()->route('admin.themes.index')
            ->with('success', 'Theme deleted successfully');
    }

    public function duplicate(Theme $theme)
    {
        $newTheme = $theme->replicate();
        $newTheme->name = $theme->name . ' (Copy)';
        $newTheme->slug = Str::slug($newTheme->name);
        $newTheme->is_active = false;
        $newTheme->save();

        return redirect()->route('admin.themes.edit', $newTheme)
            ->with('success', 'Theme duplicated successfully. Please update the details.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,make_premium,make_free,delete',
            'theme_ids' => 'required|array',
            'theme_ids.*' => 'exists:themes,id'
        ]);

        $themes = Theme::whereIn('id', $request->theme_ids);

        switch ($request->action) {
            case 'activate':
                $themes->update(['is_active' => true]);
                $message = 'Themes activated successfully';
                break;
            case 'deactivate':
                $themes->update(['is_active' => false]);
                $message = 'Themes deactivated successfully';
                break;
            case 'make_premium':
                $themes->update(['is_premium' => true]);
                $message = 'Themes marked as premium successfully';
                break;
            case 'make_free':
                $themes->update(['is_premium' => false]);
                $message = 'Themes marked as free successfully';
                break;
            case 'delete':
                // Check for templates before deleting
                $themesWithTemplates = $themes->withCount('templates')
                    ->having('templates_count', '>', 0)
                    ->count();
                
                if ($themesWithTemplates > 0) {
                    return redirect()->route('admin.themes.index')
                        ->with('error', 'Cannot delete themes with associated templates.');
                }
                
                $themes->each(function($theme) {
                    if ($theme->background_image) {
                        Storage::disk('public')->delete($theme->background_image);
                    }
                    if ($theme->preview_image) {
                        Storage::disk('public')->delete($theme->preview_image);
                    }
                });
                
                $themes->delete();
                $message = 'Themes deleted successfully';
                break;
        }

        return redirect()->route('admin.themes.index')
            ->with('success', $message);
    }
}