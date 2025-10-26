<?php

namespace App\Http\Controllers;

use App\Services\RealtimeActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RealtimeActivityController extends Controller
{
    protected $realtimeActivityService;

    public function __construct(RealtimeActivityService $realtimeActivityService)
    {
        $this->realtimeActivityService = $realtimeActivityService;
        
        $this->middleware('auth');
    }

    /**
     * Get all user activities
     */
    public function getAllUserActivities(Request $request)
    {
        $limit = $request->get('limit', 50);
        $activities = $this->realtimeActivityService->getAllUserActivities($limit);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get activities for current user
     */
    public function getMyActivities(Request $request)
    {
        $limit = $request->get('limit', 20);
        $activities = $this->realtimeActivityService->getUserActivities(Auth::id(), $limit);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get activities for a specific user
     */
    public function getUserActivities(Request $request, $userId)
    {
        $limit = $request->get('limit', 20);
        $activities = $this->realtimeActivityService->getUserActivities($userId, $limit);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get activities for current user's branch
     */
    public function getBranchActivities(Request $request)
    {
        $limit = $request->get('limit', 30);
        $branchId = Auth::user()->branch_id;
        
        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not assigned to a branch',
            ], 400);
        }

        $activities = $this->realtimeActivityService->getBranchActivities($branchId, $limit);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get financial activities
     */
    public function getFinancialActivities(Request $request)
    {
        $limit = $request->get('limit', 20);
        $activities = $this->realtimeActivityService->getFinancialActivities($limit);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get activity statistics
     */
    public function getActivityStatistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfDay());
        $endDate = $request->get('end_date', now()->endOfDay());

        $statistics = $this->realtimeActivityService->getActivityStatistics($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get pending approval notifications
     */
    public function getPendingApprovalNotifications()
    {
        $notifications = $this->realtimeActivityService->getPendingApprovalNotifications();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Get system health indicators
     */
    public function getSystemHealthIndicators()
    {
        $indicators = $this->realtimeActivityService->getSystemHealthIndicators();

        return response()->json([
            'success' => true,
            'data' => $indicators,
        ]);
    }

    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        $activeUsers = $this->realtimeActivityService->getActiveUsers();

        return response()->json([
            'success' => true,
            'data' => $activeUsers,
        ]);
    }

    /**
     * Get branch activity summary
     */
    public function getBranchActivitySummary()
    {
        $summary = $this->realtimeActivityService->getBranchActivitySummary();

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get real-time updates
     */
    public function getRealtimeUpdates(Request $request)
    {
        $lastActivityId = $request->get('last_activity_id', 0);
        $updates = $this->realtimeActivityService->getRealtimeUpdates($lastActivityId);

        return response()->json([
            'success' => true,
            'data' => $updates,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Clear activity cache
     */
    public function clearActivityCache()
    {
        $this->realtimeActivityService->clearActivityCache();

        return response()->json([
            'success' => true,
            'message' => 'Activity cache cleared successfully',
        ]);
    }

    /**
     * Get activity feed for dashboard
     */
    public function getActivityFeed(Request $request)
    {
        $type = $request->get('type', 'all'); // all, financial, user, branch
        $limit = $request->get('limit', 10);

        $activities = [];

        switch ($type) {
            case 'financial':
                $activities = $this->realtimeActivityService->getFinancialActivities($limit);
                break;
            case 'user':
                $activities = $this->realtimeActivityService->getUserActivities(Auth::id(), $limit);
                break;
            case 'branch':
                $branchId = Auth::user()->branch_id;
                if ($branchId) {
                    $activities = $this->realtimeActivityService->getBranchActivities($branchId, $limit);
                }
                break;
            default:
                $activities = $this->realtimeActivityService->getAllUserActivities($limit);
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get activity summary for dashboard widgets
     */
    public function getActivitySummary()
    {
        $summary = [
            'recent_activities' => $this->realtimeActivityService->getAllUserActivities(5),
            'pending_approvals' => $this->realtimeActivityService->getPendingApprovalNotifications(),
            'system_health' => $this->realtimeActivityService->getSystemHealthIndicators(),
            'active_users' => $this->realtimeActivityService->getActiveUsers(),
            'statistics' => $this->realtimeActivityService->getActivityStatistics(),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
