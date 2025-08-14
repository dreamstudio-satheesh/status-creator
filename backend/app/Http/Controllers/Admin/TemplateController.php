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
        $query = Template::with('theme')->withCount(['userCreations']);

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

        // Note: Type filtering removed as 'type' column doesn't exist

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
            'with_user_creations' => Template::has('userCreations')->count(),
        ];

        $themes = Theme::where('is_active', true)->orderBy('name')->get();

        return view('admin.templates.index', compact('templates', 'stats', 'themes'));
    }

    public function show(Template $template)
    {
        $template->load(['theme', 'userCreations' => function($q) {
            $q->with('user')->latest()->limit(10);
        }]);

        $stats = [
            'usage_count' => $template->userCreations()->count(),
            'recent_usage' => $template->userCreations()->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.templates.show', compact('template', 'stats'));
    }

    public function create()
    {
        $themes = Theme::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.templates.create', compact('themes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'title' => 'required|string|max:255',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'quote_text' => 'required|string',
            'quote_text_ta' => 'nullable|string',
            'font_family' => 'nullable|string|max:100',
            'font_size' => 'nullable|integer|min:8|max:72',
            'text_color' => 'nullable|string|max:7',
            'text_alignment' => 'nullable|in:left,center,right',
            'padding' => 'nullable|integer|min:0|max:100',
            'is_premium' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'image_caption' => 'nullable|string',
        ]);

        $templateData = [
            'theme_id' => $request->theme_id,
            'title' => $request->title,
            'quote_text' => $request->quote_text,
            'quote_text_ta' => $request->quote_text_ta,
            'font_family' => $request->font_family,
            'font_size' => $request->font_size,
            'text_color' => $request->text_color,
            'text_alignment' => $request->text_alignment ?? 'center',
            'padding' => $request->padding ?? 20,
            'is_premium' => $request->boolean('is_premium'),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'ai_generated' => false,
            'usage_count' => 0,
            'image_caption' => $request->image_caption,
        ];

        // Handle background image upload
        if ($request->hasFile('background_image')) {
            $templateData['background_image'] = $request->file('background_image')
                ->store('templates/backgrounds', 'public');
        }

        $template = Template::create($templateData);

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Template created successfully');
    }

    public function edit(Template $template)
    {
        $themes = Theme::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.templates.edit', compact('template', 'themes'));
    }

    public function update(Request $request, Template $template)
    {
        $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'title' => 'required|string|max:255',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'quote_text' => 'required|string',
            'quote_text_ta' => 'nullable|string',
            'font_family' => 'nullable|string|max:100',
            'font_size' => 'nullable|integer|min:8|max:72',
            'text_color' => 'nullable|string|max:7',
            'text_alignment' => 'nullable|in:left,center,right',
            'padding' => 'nullable|integer|min:0|max:100',
            'is_premium' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'image_caption' => 'nullable|string',
        ]);

        $templateData = [
            'theme_id' => $request->theme_id,
            'title' => $request->title,
            'quote_text' => $request->quote_text,
            'quote_text_ta' => $request->quote_text_ta,
            'font_family' => $request->font_family,
            'font_size' => $request->font_size,
            'text_color' => $request->text_color,
            'text_alignment' => $request->text_alignment ?? 'center',
            'padding' => $request->padding ?? 20,
            'is_premium' => $request->boolean('is_premium'),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'image_caption' => $request->image_caption,
        ];

        // Handle background image upload
        if ($request->hasFile('background_image')) {
            // Delete old image
            if ($template->background_image) {
                Storage::disk('public')->delete($template->background_image);
            }
            $templateData['background_image'] = $request->file('background_image')
                ->store('templates/backgrounds', 'public');
        }

        $template->update($templateData);

        return redirect()->route('admin.templates.show', $template)
            ->with('success', 'Template updated successfully');
    }

    public function destroy(Template $template)
    {
        // Delete associated files
        if ($template->background_image) {
            Storage::disk('public')->delete($template->background_image);
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
            'count' => 'required|integer|min:1|max:50',
            'prompts' => 'required|array|min:1',
            'prompts.*' => 'required|string|max:500',
        ]);

        $job = BulkAIGeneration::dispatch(
            $request->theme_id,
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
        $usage = $template->userCreations()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'usage_chart' => $usage,
            'total_usage' => $template->userCreations()->count(),
        ]);
    }
}