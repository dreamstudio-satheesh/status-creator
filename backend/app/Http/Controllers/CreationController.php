<?php

namespace App\Http\Controllers;

use App\Models\UserCreation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = UserCreation::where('user_id', $user->id)
            ->with(['theme', 'template'])
            ->latest();

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search by content
        if ($request->has('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        $creations = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $creations,
            'message' => 'User creations retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'type' => 'required|in:status,quote,caption',
            'theme_id' => 'nullable|exists:themes,id',
            'template_id' => 'nullable|exists:templates,id',
            'metadata' => 'nullable|array',
        ]);

        $user = Auth::user();

        $creation = UserCreation::create([
            'user_id' => $user->id,
            'content' => $request->content,
            'type' => $request->type,
            'theme_id' => $request->theme_id,
            'template_id' => $request->template_id,
            'metadata' => $request->metadata ?? [],
            'is_shared' => false,
        ]);

        $creation->load(['theme', 'template']);

        return response()->json([
            'success' => true,
            'data' => $creation,
            'message' => 'Creation saved successfully'
        ], 201);
    }

    public function show(UserCreation $creation)
    {
        $user = Auth::user();

        // Check if the creation belongs to the authenticated user
        if ($creation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $creation->load(['theme', 'template', 'user']);

        return response()->json([
            'success' => true,
            'data' => $creation,
            'message' => 'Creation retrieved successfully'
        ]);
    }

    public function update(Request $request, UserCreation $creation)
    {
        $user = Auth::user();

        // Check if the creation belongs to the authenticated user
        if ($creation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'content' => 'sometimes|required|string|max:2000',
            'metadata' => 'nullable|array',
        ]);

        $creation->update($request->only(['content', 'metadata']));

        $creation->load(['theme', 'template']);

        return response()->json([
            'success' => true,
            'data' => $creation,
            'message' => 'Creation updated successfully'
        ]);
    }

    public function destroy(UserCreation $creation)
    {
        $user = Auth::user();

        // Check if the creation belongs to the authenticated user
        if ($creation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $creation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Creation deleted successfully'
        ]);
    }

    public function share(Request $request, UserCreation $creation)
    {
        $user = Auth::user();

        // Check if the creation belongs to the authenticated user
        if ($creation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'is_shared' => 'required|boolean',
        ]);

        $creation->update([
            'is_shared' => $request->is_shared,
            'shared_at' => $request->is_shared ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $creation,
            'message' => $request->is_shared ? 'Creation shared successfully' : 'Creation unshared successfully'
        ]);
    }
}