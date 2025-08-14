<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    public function index(Request $request)
    {
        $query = Theme::query()->where('is_active', true);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('name_ta', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $themes = $query->orderBy('order_index', 'asc')
                       ->orderBy('name', 'asc')
                       ->get()
                       ->map(function ($theme) {
                           return [
                               'id' => $theme->id,
                               'name' => $theme->name,
                               'slug' => $theme->slug,
                               'name_ta' => $theme->name_ta,
                               'description' => $theme->description,
                               'icon' => $theme->icon,
                               'color' => $theme->color,
                               'template_count' => $theme->templates()->where('is_active', true)->count(),
                               'premium_template_count' => $theme->templates()
                                   ->where('is_active', true)
                                   ->where('is_premium', true)
                                   ->count(),
                           ];
                       });

        return response()->json([
            'success' => true,
            'themes' => $themes,
            'total_count' => $themes->count(),
        ]);
    }

    public function show(Request $request, Theme $theme)
    {
        if (!$theme->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found or inactive',
            ], 404);
        }

        $user = $request->user();
        $isPremium = $user ? $user->isPremium() : false;

        $templatesQuery = $theme->templates()->where('is_active', true);
        
        if (!$isPremium) {
            $templatesQuery->where('is_premium', false);
        }

        $templates = $templatesQuery->orderBy('is_featured', 'desc')
                                   ->orderBy('usage_count', 'desc')
                                   ->orderBy('created_at', 'desc')
                                   ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'theme' => [
                'id' => $theme->id,
                'name' => $theme->name,
                'slug' => $theme->slug,
                'name_ta' => $theme->name_ta,
                'description' => $theme->description,
                'icon' => $theme->icon,
                'color' => $theme->color,
            ],
            'templates' => $templates->items(),
            'pagination' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
                'has_more' => $templates->hasMorePages(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'name_ta' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|url|max:255',
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'order_index' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slug = \Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        while (Theme::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $theme = Theme::create([
            'name' => $request->name,
            'slug' => $slug,
            'name_ta' => $request->name_ta,
            'description' => $request->description,
            'icon' => $request->icon,
            'color' => $request->color ?? '#007bff',
            'order_index' => $request->order_index ?? 0,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Theme created successfully',
            'theme' => $theme,
        ], 201);
    }

    public function update(Request $request, Theme $theme)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'name_ta' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|url|max:255',
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'order_index' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $updateData = $request->only([
            'name', 'name_ta', 'description', 'icon', 'color', 'order_index', 'is_active'
        ]);

        if (isset($updateData['name']) && $updateData['name'] !== $theme->name) {
            $slug = \Str::slug($updateData['name']);
            $originalSlug = $slug;
            $counter = 1;

            while (Theme::where('slug', $slug)->where('id', '!=', $theme->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $updateData['slug'] = $slug;
        }

        $theme->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Theme updated successfully',
            'theme' => $theme->fresh(),
        ]);
    }

    public function destroy(Theme $theme)
    {
        if ($theme->templates()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete theme with existing templates',
            ], 422);
        }

        $theme->delete();

        return response()->json([
            'success' => true,
            'message' => 'Theme deleted successfully',
        ]);
    }

    public function toggleStatus(Theme $theme)
    {
        $theme->update(['is_active' => !$theme->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Theme status updated successfully',
            'theme' => $theme->fresh(),
        ]);
    }

    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'themes' => 'required|array',
            'themes.*.id' => 'required|integer|exists:themes,id',
            'themes.*.order_index' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->themes as $themeData) {
            Theme::where('id', $themeData['id'])
                 ->update(['order_index' => $themeData['order_index']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Theme order updated successfully',
        ]);
    }
}