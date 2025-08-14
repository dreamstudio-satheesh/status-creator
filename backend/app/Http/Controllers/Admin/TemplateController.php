<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\Theme;
use App\Jobs\BulkAIGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = Template::with('theme')->withCount(['statusHistories', 'ratings']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        // Filter by theme
        if ($request->has('theme_id') && $request->theme_id) {
            $query->where('theme_id', $request->theme_id);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by premium status
        if ($request->has('premium')) {
            if ($request->premium === 'free') {
                $query->where('is_premium', false);
            } elseif ($request->premium === 'premium') {
                $query->where('is_premium', true);
            }
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Template::count(),
            'active' => Template::where('is_active', true)->count(),
            'premium' => Template::where('is_premium', true)->count(),
            'avg_rating' => Template::whereHas('ratings')->withAvg('ratings', 'rating')->get()->avg('ratings_avg_rating') ?? 0,
        ];

        $themes = Theme::where('is_active', true)->orderBy('name')->get();
        $types = Template::distinct('type')->pluck('type')->filter();

        return view('admin.templates.index', compact('templates', 'stats', 'themes', 'types'));
    }

    public function show(Template $template)
    {
        $template->load(['theme', 'ratings.user', 'statusHistories' => function($q) {
            $q->with('user')->latest()->limit(10);
        }]);

        $stats = [
            'usage_count' => $template->statusHistories()->count(),
            'avg_rating' => $template->ratings()->avg('rating') ?? 0,
            'total_ratings' => $template->ratings()->count(),
            'recent_usage' => $template->statusHistories()->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.templates.show', compact('template', 'stats'));
    }

    public function create()
    {
        $themes = Theme::where('is_active', true)->orderBy('name')->get();
        $types = ['status', 'quote', 'greeting', 'motivational', 'funny', 'love', 'friendship'];
        
        return view('admin.templates.create', compact('themes', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'theme_id' => 'required|exists:themes,id',
            'type' => 'required|string|max:50',
            'tags' => 'nullable|string',
            'preview_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $templateData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'content' => $request->content,
            'theme_id' => $request->theme_id,
            'type' => $request->type,
            'tags' => $request->tags,
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => $request->sort_order ?? 0,
        ];

        // Handle preview image upload
        if ($request->hasFile('preview_image')) {
            $templateData['preview_image'] = $request->file('preview_image')
                ->store('templates/previews', 'public');
        }

        $template = Template::create($templateData);

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Template created successfully');
    }

    public function edit(Template $template)
    {
        $themes = Theme::where('is_active', true)->orderBy('name')->get();
        $types = ['status', 'quote', 'greeting', 'motivational', 'funny', 'love', 'friendship'];
        
        return view('admin.templates.edit', compact('template', 'themes', 'types'));
    }

    public function update(Request $request, Template $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'theme_id' => 'required|exists:themes,id',
            'type' => 'required|string|max:50',
            'tags' => 'nullable|string',
            'preview_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $templateData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'content' => $request->content,
            'theme_id' => $request->theme_id,
            'type' => $request->type,
            'tags' => $request->tags,
            'is_premium' => $request->boolean('is_premium'),
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => $request->sort_order ?? 0,
        ];

        // Handle preview image upload
        if ($request->hasFile('preview_image')) {
            // Delete old image
            if ($template->preview_image) {
                Storage::disk('public')->delete($template->preview_image);
            }
            $templateData['preview_image'] = $request->file('preview_image')
                ->store('templates/previews', 'public');
        }

        $template->update($templateData);

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Template updated successfully');
    }

    public function destroy(Template $template)
    {
        // Delete associated files
        if ($template->preview_image) {
            Storage::disk('public')->delete($template->preview_image);
        }

        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template deleted successfully');
    }

    public function duplicate(Template $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->slug = Str::slug($newTemplate->name);
        $newTemplate->is_active = false;
        $newTemplate->save();

        return redirect()->route('admin.templates.edit', $newTemplate)
            ->with('success', 'Template duplicated successfully. Please update the details.');
    }

    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'type' => 'required|string',
            'count' => 'required|integer|min:1|max:50',
            'prompts' => 'required|array|min:1',
            'prompts.*' => 'required|string|max:500',
        ]);

        $job = BulkAIGeneration::dispatch(
            $request->theme_id,
            $request->type,
            $request->prompts,
            $request->count,
            auth('admin')->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Bulk generation job started. Templates will be created in the background.',
            'job_id' => $job->getJobId()
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,make_premium,make_free,feature,unfeature,delete',
            'template_ids' => 'required|array',
            'template_ids.*' => 'exists:templates,id'
        ]);

        $templates = Template::whereIn('id', $request->template_ids);

        switch ($request->action) {
            case 'activate':
                $templates->update(['is_active' => true]);
                $message = 'Templates activated successfully';
                break;
            case 'deactivate':
                $templates->update(['is_active' => false]);
                $message = 'Templates deactivated successfully';
                break;
            case 'make_premium':
                $templates->update(['is_premium' => true]);
                $message = 'Templates marked as premium successfully';
                break;
            case 'make_free':
                $templates->update(['is_premium' => false]);
                $message = 'Templates marked as free successfully';
                break;
            case 'feature':
                $templates->update(['is_featured' => true]);
                $message = 'Templates featured successfully';
                break;
            case 'unfeature':
                $templates->update(['is_featured' => false]);
                $message = 'Templates unfeatured successfully';
                break;
            case 'delete':
                $templates->each(function($template) {
                    if ($template->preview_image) {
                        Storage::disk('public')->delete($template->preview_image);
                    }
                });
                $templates->delete();
                $message = 'Templates deleted successfully';
                break;
        }

        return redirect()->route('admin.templates.index')
            ->with('success', $message);
    }

    public function analytics(Template $template)
    {
        $usage = $template->statusHistories()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $ratings = $template->ratings()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();

        return response()->json([
            'usage_chart' => $usage,
            'ratings_chart' => $ratings,
            'total_usage' => $template->statusHistories()->count(),
            'avg_rating' => $template->ratings()->avg('rating'),
        ]);
    }
}