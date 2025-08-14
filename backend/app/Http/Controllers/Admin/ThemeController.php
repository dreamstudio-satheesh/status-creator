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
        $query = Theme::withCount(['templates']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ta', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
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

        $themes = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Theme::count(),
            'active' => Theme::where('is_active', true)->count(),
            'inactive' => Theme::where('is_active', false)->count(),
            'templates' => \App\Models\Template::count(),
        ];

        return view('admin.themes.index', compact('themes', 'stats'));
    }

    public function show(Theme $theme)
    {
        $theme->load(['templates' => function($q) {
            $q->withCount('userCreations')->orderBy('created_at', 'desc');
        }]);

        $stats = [
            'total_templates' => $theme->templates()->count(),
            'active_templates' => $theme->templates()->where('is_active', true)->count(),
            'usage_count' => $theme->templates()->withCount('userCreations')->get()->sum('user_creations_count'),
            'recent_usage' => $theme->templates()
                ->withCount(['userCreations' => function($q) {
                    $q->where('created_at', '>=', now()->subDays(7));
                }])
                ->get()
                ->sum('user_creations_count'),
        ];

        return view('admin.themes.show', compact('theme', 'stats'));
    }

    public function create()
    {
        return view('admin.themes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:themes,name',
            'name_ta' => 'nullable|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'order_index' => 'nullable|integer',
        ]);

        $themeData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'name_ta' => $request->name_ta,
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color,
            'is_active' => $request->boolean('is_active', true),
            'order_index' => $request->order_index ?? 0,
        ];

        // Note: File uploads removed as they're not part of the current Theme model

        $theme = Theme::create($themeData);

        return redirect()->route('admin.themes.show', $theme)
            ->with('success', 'Theme created successfully');
    }

    public function edit(Theme $theme)
    {
        return view('admin.themes.edit', compact('theme'));
    }

    public function update(Request $request, Theme $theme)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:themes,name,' . $theme->id,
            'name_ta' => 'nullable|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'order_index' => 'nullable|integer',
        ]);

        $themeData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'name_ta' => $request->name_ta,
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color,
            'is_active' => $request->boolean('is_active'),
            'order_index' => $request->order_index ?? 0,
        ];

        // Note: File uploads removed as they're not part of the current Theme model

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

        // Note: No files to delete as they're not part of the current Theme model

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
            'action' => 'required|in:activate,deactivate,delete',
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
            case 'delete':
                // Check for templates before deleting
                $themesWithTemplates = $themes->withCount('templates')
                    ->having('templates_count', '>', 0)
                    ->count();
                
                if ($themesWithTemplates > 0) {
                    return redirect()->route('admin.themes.index')
                        ->with('error', 'Cannot delete themes with associated templates.');
                }
                
                // Note: No files to delete as they're not part of the current Theme model
                
                $themes->delete();
                $message = 'Themes deleted successfully';
                break;
        }

        return redirect()->route('admin.themes.index')
            ->with('success', $message);
    }
}