<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Theme;
use App\Models\UserCreation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isPremium = $user ? $user->isPremium() : false;

        $query = Template::with('theme:id,name,name_ta,slug,color')
                        ->where('is_active', true);

        if (!$isPremium) {
            $query->where('is_premium', false);
        }

        if ($request->has('theme_id')) {
            $query->where('theme_id', $request->theme_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('quote_text', 'LIKE', "%{$search}%")
                  ->orWhere('quote_text_ta', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->has('ai_generated')) {
            $query->where('ai_generated', $request->boolean('ai_generated'));
        }

        $sortBy = $request->get('sort_by', 'featured_usage');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_used':
                $query->orderBy('usage_count', 'desc');
                break;
            case 'featured_usage':
            default:
                $query->orderBy('is_featured', 'desc')
                      ->orderBy('usage_count', 'desc');
                break;
        }

        $templates = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
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

    public function show(Request $request, Template $template)
    {
        if (!$template->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or inactive',
            ], 404);
        }

        $user = $request->user();
        $isPremium = $user ? $user->isPremium() : false;

        if ($template->is_premium && !$isPremium) {
            return response()->json([
                'success' => false,
                'message' => 'This template requires a premium subscription',
                'is_premium_required' => true,
            ], 403);
        }

        $template->load('theme:id,name,name_ta,slug,color');

        $isFavorited = false;
        if ($user) {
            $isFavorited = DB::table('user_favorites')
                            ->where('user_id', $user->id)
                            ->where('template_id', $template->id)
                            ->exists();
        }

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $template->id,
                'theme' => $template->theme,
                'title' => $template->title,
                'background_image' => $template->background_image,
                'quote_text' => $template->quote_text,
                'quote_text_ta' => $template->quote_text_ta,
                'font_family' => $template->font_family,
                'font_size' => $template->font_size,
                'text_color' => $template->text_color,
                'text_alignment' => $template->text_alignment,
                'padding' => $template->padding,
                'is_premium' => $template->is_premium,
                'is_featured' => $template->is_featured,
                'usage_count' => $template->usage_count,
                'ai_generated' => $template->ai_generated,
                'image_caption' => $template->image_caption,
                'is_favorited' => $isFavorited,
                'created_at' => $template->created_at,
            ],
        ]);
    }

    public function byTheme(Request $request, Theme $theme)
    {
        if (!$theme->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found or inactive',
            ], 404);
        }

        $user = $request->user();
        $isPremium = $user ? $user->isPremium() : false;

        $query = $theme->templates()->where('is_active', true);

        if (!$isPremium) {
            $query->where('is_premium', false);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('quote_text', 'LIKE', "%{$search}%")
                  ->orWhere('quote_text_ta', 'LIKE', "%{$search}%");
            });
        }

        $templates = $query->orderBy('is_featured', 'desc')
                          ->orderBy('usage_count', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'theme' => [
                'id' => $theme->id,
                'name' => $theme->name,
                'name_ta' => $theme->name_ta,
                'slug' => $theme->slug,
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

    public function featured(Request $request)
    {
        $user = $request->user();
        $isPremium = $user ? $user->isPremium() : false;

        $query = Template::with('theme:id,name,name_ta,slug,color')
                        ->where('is_active', true)
                        ->where('is_featured', true);

        if (!$isPremium) {
            $query->where('is_premium', false);
        }

        $templates = $query->orderBy('usage_count', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'featured_templates' => $templates->items(),
            'pagination' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
                'has_more' => $templates->hasMorePages(),
            ],
        ]);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100',
            'theme_id' => 'nullable|integer|exists:themes,id',
            'is_premium' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $isPremium = $user ? $user->isPremium() : false;
        $search = $request->q;

        $query = Template::with('theme:id,name,name_ta,slug,color')
                        ->where('is_active', true)
                        ->where(function ($q) use ($search) {
                            $q->where('title', 'LIKE', "%{$search}%")
                              ->orWhere('quote_text', 'LIKE', "%{$search}%")
                              ->orWhere('quote_text_ta', 'LIKE', "%{$search}%")
                              ->orWhere('image_caption', 'LIKE', "%{$search}%");
                        });

        if (!$isPremium) {
            $query->where('is_premium', false);
        }

        if ($request->has('theme_id')) {
            $query->where('theme_id', $request->theme_id);
        }

        if ($request->has('is_premium')) {
            $query->where('is_premium', $request->boolean('is_premium'));
        }

        $templates = $query->orderBy('is_featured', 'desc')
                          ->orderBy('usage_count', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'search_query' => $search,
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

    public function useTemplate(Request $request, Template $template)
    {
        if (!$template->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or inactive',
            ], 404);
        }

        $user = $request->user();
        $isPremium = $user ? $user->isPremium() : false;

        if ($template->is_premium && !$isPremium) {
            return response()->json([
                'success' => false,
                'message' => 'This template requires a premium subscription',
                'is_premium_required' => true,
            ], 403);
        }

        $template->increment('usage_count');

        return response()->json([
            'success' => true,
            'message' => 'Template usage recorded',
            'template' => [
                'id' => $template->id,
                'title' => $template->title,
                'background_image' => $template->background_image,
                'quote_text' => $template->quote_text,
                'quote_text_ta' => $template->quote_text_ta,
                'font_family' => $template->font_family,
                'font_size' => $template->font_size,
                'text_color' => $template->text_color,
                'text_alignment' => $template->text_alignment,
                'padding' => $template->padding,
                'usage_count' => $template->usage_count,
            ],
        ]);
    }

    public function toggleFavorite(Request $request, Template $template)
    {
        $user = $request->user();

        if (!$template->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or inactive',
            ], 404);
        }

        $favorite = DB::table('user_favorites')
                     ->where('user_id', $user->id)
                     ->where('template_id', $template->id)
                     ->first();

        if ($favorite) {
            DB::table('user_favorites')
              ->where('user_id', $user->id)
              ->where('template_id', $template->id)
              ->delete();

            $isFavorited = false;
            $message = 'Template removed from favorites';
        } else {
            DB::table('user_favorites')->insert([
                'user_id' => $user->id,
                'template_id' => $template->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $isFavorited = true;
            $message = 'Template added to favorites';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorited' => $isFavorited,
        ]);
    }

    public function favorites(Request $request)
    {
        $user = $request->user();

        $templates = Template::with('theme:id,name,name_ta,slug,color')
                            ->join('user_favorites', 'templates.id', '=', 'user_favorites.template_id')
                            ->where('user_favorites.user_id', $user->id)
                            ->where('templates.is_active', true)
                            ->orderBy('user_favorites.created_at', 'desc')
                            ->select('templates.*')
                            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'favorites' => $templates->items(),
            'pagination' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
                'has_more' => $templates->hasMorePages(),
            ],
        ]);
    }

    public function rateTemplate(Request $request, Template $template)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$template->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or inactive',
            ], 404);
        }

        $user = $request->user();

        $existingRating = DB::table('template_ratings')
                           ->where('user_id', $user->id)
                           ->where('template_id', $template->id)
                           ->first();

        if ($existingRating) {
            DB::table('template_ratings')
              ->where('user_id', $user->id)
              ->where('template_id', $template->id)
              ->update([
                  'rating' => $request->rating,
                  'review' => $request->review,
                  'updated_at' => now(),
              ]);

            $message = 'Rating updated successfully';
        } else {
            DB::table('template_ratings')->insert([
                'user_id' => $user->id,
                'template_id' => $template->id,
                'rating' => $request->rating,
                'review' => $request->review,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $message = 'Rating added successfully';
        }

        $avgRating = DB::table('template_ratings')
                      ->where('template_id', $template->id)
                      ->avg('rating');

        $ratingCount = DB::table('template_ratings')
                        ->where('template_id', $template->id)
                        ->count();

        return response()->json([
            'success' => true,
            'message' => $message,
            'user_rating' => $request->rating,
            'average_rating' => round($avgRating, 2),
            'rating_count' => $ratingCount,
        ]);
    }

    public function getTemplateRatings(Request $request, Template $template)
    {
        if (!$template->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or inactive',
            ], 404);
        }

        $ratings = DB::table('template_ratings')
                    ->join('users', 'template_ratings.user_id', '=', 'users.id')
                    ->where('template_ratings.template_id', $template->id)
                    ->whereNotNull('template_ratings.review')
                    ->select([
                        'template_ratings.rating',
                        'template_ratings.review',
                        'template_ratings.created_at',
                        'users.name as user_name',
                        'users.avatar as user_avatar'
                    ])
                    ->orderBy('template_ratings.created_at', 'desc')
                    ->paginate($request->get('per_page', 10));

        $avgRating = DB::table('template_ratings')
                      ->where('template_id', $template->id)
                      ->avg('rating');

        $ratingCount = DB::table('template_ratings')
                        ->where('template_id', $template->id)
                        ->count();

        $ratingDistribution = DB::table('template_ratings')
                               ->where('template_id', $template->id)
                               ->select('rating', DB::raw('count(*) as count'))
                               ->groupBy('rating')
                               ->orderBy('rating', 'desc')
                               ->pluck('count', 'rating')
                               ->toArray();

        $user = $request->user();
        $userRating = null;
        if ($user) {
            $userRating = DB::table('template_ratings')
                           ->where('user_id', $user->id)
                           ->where('template_id', $template->id)
                           ->value('rating');
        }

        return response()->json([
            'success' => true,
            'ratings_summary' => [
                'average_rating' => round($avgRating, 2),
                'rating_count' => $ratingCount,
                'user_rating' => $userRating,
                'distribution' => [
                    '5' => $ratingDistribution[5] ?? 0,
                    '4' => $ratingDistribution[4] ?? 0,
                    '3' => $ratingDistribution[3] ?? 0,
                    '2' => $ratingDistribution[2] ?? 0,
                    '1' => $ratingDistribution[1] ?? 0,
                ],
            ],
            'reviews' => $ratings->items(),
            'pagination' => [
                'current_page' => $ratings->currentPage(),
                'last_page' => $ratings->lastPage(),
                'per_page' => $ratings->perPage(),
                'total' => $ratings->total(),
                'has_more' => $ratings->hasMorePages(),
            ],
        ]);
    }
}