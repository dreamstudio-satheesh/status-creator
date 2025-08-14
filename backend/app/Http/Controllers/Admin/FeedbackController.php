<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $query = UserFeedback::with('user');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('subject', 'like', "%{$search}%")
                ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort by latest first
        $query->orderBy('created_at', 'desc');

        $feedback = $query->paginate(15);

        // Get stats
        $stats = Cache::remember('admin_feedback_stats', 300, function () {
            return [
                'total' => UserFeedback::count(),
                'unread' => UserFeedback::where('status', 'unread')->count(),
                'read' => UserFeedback::where('status', 'read')->count(),
                'resolved' => UserFeedback::where('status', 'resolved')->count(),
                'avg_rating' => UserFeedback::avg('rating') ?? 0,
                'ratings_distribution' => [
                    5 => UserFeedback::where('rating', 5)->count(),
                    4 => UserFeedback::where('rating', 4)->count(),
                    3 => UserFeedback::where('rating', 3)->count(),
                    2 => UserFeedback::where('rating', 2)->count(),
                    1 => UserFeedback::where('rating', 1)->count(),
                ],
                'recent_count' => UserFeedback::where('created_at', '>=', now()->subWeek())->count(),
            ];
        });

        return view('admin.feedback.index', compact('feedback', 'stats'));
    }

    public function show(UserFeedback $feedback)
    {
        $feedback->load('user');
        
        // Mark as read if unread
        if ($feedback->status === 'unread') {
            $feedback->update(['status' => 'read']);
        }

        return view('admin.feedback.show', compact('feedback'));
    }

    public function update(Request $request, UserFeedback $feedback)
    {
        $request->validate([
            'status' => 'required|in:unread,read,resolved',
            'admin_response' => 'nullable|string|max:1000',
        ]);

        $feedback->update([
            'status' => $request->status,
            'admin_response' => $request->admin_response,
            'responded_at' => $request->filled('admin_response') ? now() : null,
        ]);

        return back()->with('success', 'Feedback updated successfully.');
    }

    public function destroy(UserFeedback $feedback)
    {
        $feedback->delete();

        return redirect()
            ->route('admin.feedback.index')
            ->with('success', 'Feedback deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_read,mark_resolved,delete',
            'feedback_ids' => 'required|array',
            'feedback_ids.*' => 'exists:user_feedback,id',
        ]);

        $feedbackIds = $request->feedback_ids;

        switch ($request->action) {
            case 'mark_read':
                UserFeedback::whereIn('id', $feedbackIds)
                    ->update(['status' => 'read']);
                $message = 'Feedback marked as read.';
                break;
                
            case 'mark_resolved':
                UserFeedback::whereIn('id', $feedbackIds)
                    ->update(['status' => 'resolved']);
                $message = 'Feedback marked as resolved.';
                break;
                
            case 'delete':
                UserFeedback::whereIn('id', $feedbackIds)->delete();
                $message = 'Feedback deleted successfully.';
                break;
        }

        return back()->with('success', $message);
    }

    public function export(Request $request)
    {
        $query = UserFeedback::with('user');

        // Apply same filters as index
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $feedback = $query->orderBy('created_at', 'desc')->get();

        $filename = 'feedback_export_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($feedback) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'User Name',
                'User Email',
                'Subject',
                'Message',
                'Rating',
                'Status',
                'Created At',
                'Admin Response',
                'Responded At'
            ]);

            // CSV data
            foreach ($feedback as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->user->name ?? 'N/A',
                    $item->user->email ?? 'N/A',
                    $item->subject,
                    $item->message,
                    $item->rating,
                    $item->status,
                    $item->created_at->format('Y-m-d H:i:s'),
                    $item->admin_response ?? '',
                    $item->responded_at ? $item->responded_at->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function analytics()
    {
        $analytics = Cache::remember('feedback_analytics', 600, function () {
            $totalFeedback = UserFeedback::count();
            
            return [
                'summary' => [
                    'total' => $totalFeedback,
                    'avg_rating' => UserFeedback::avg('rating') ?? 0,
                    'response_rate' => $totalFeedback > 0 
                        ? (UserFeedback::whereNotNull('admin_response')->count() / $totalFeedback) * 100 
                        : 0,
                    'resolution_rate' => $totalFeedback > 0 
                        ? (UserFeedback::where('status', 'resolved')->count() / $totalFeedback) * 100 
                        : 0,
                ],
                'ratings_trend' => $this->getRatingsTrend(),
                'monthly_feedback' => $this->getMonthlyFeedback(),
                'top_issues' => $this->getTopIssues(),
                'satisfaction_score' => $this->calculateSatisfactionScore(),
            ];
        });

        return view('admin.feedback.analytics', compact('analytics'));
    }

    private function getRatingsTrend()
    {
        $days = 30;
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $avgRating = UserFeedback::whereDate('created_at', $date)->avg('rating') ?? 0;
            
            $data[] = [
                'date' => $date->format('M d'),
                'rating' => round($avgRating, 1)
            ];
        }
        
        return $data;
    }

    private function getMonthlyFeedback()
    {
        $months = 6;
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = UserFeedback::whereMonth('created_at', $date->month)
                                ->whereYear('created_at', $date->year)
                                ->count();
            
            $data[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }
        
        return $data;
    }

    private function getTopIssues()
    {
        // This would typically analyze message content for common keywords
        // For now, return sample data
        return [
            ['issue' => 'App Performance', 'count' => 45],
            ['issue' => 'Login Issues', 'count' => 32],
            ['issue' => 'Template Quality', 'count' => 28],
            ['issue' => 'Payment Problems', 'count' => 15],
            ['issue' => 'Feature Requests', 'count' => 12],
        ];
    }

    private function calculateSatisfactionScore()
    {
        $ratings = UserFeedback::selectRaw('rating, COUNT(*) as count')
                              ->groupBy('rating')
                              ->pluck('count', 'rating')
                              ->toArray();
        
        $totalResponses = array_sum($ratings);
        
        if ($totalResponses === 0) {
            return 0;
        }
        
        // Calculate NPS-like score (ratings 4-5 as promoters, 1-2 as detractors)
        $promoters = ($ratings[5] ?? 0) + ($ratings[4] ?? 0);
        $detractors = ($ratings[1] ?? 0) + ($ratings[2] ?? 0);
        
        return round((($promoters - $detractors) / $totalResponses) * 100);
    }
}