<?php

namespace App\Http\Controllers;

use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class AuditTrailController extends Controller
{
    protected $auditTrailService;

    public function __construct(AuditTrailService $auditTrailService)
    {
        $this->auditTrailService = $auditTrailService;
        
        $this->middleware('auth');
        $this->middleware('permission:view_audit_trail')->only(['index', 'show', 'getModelTrail']);
        $this->middleware('permission:export_audit_trail')->only(['export']);
    }

    /**
     * Display audit trail dashboard
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());
        $userId = $request->get('user_id');
        $logName = $request->get('log_name');

        $auditTrail = $this->auditTrailService->getSystemAuditTrail($startDate, $endDate, $userId, $logName);
        $statistics = $this->auditTrailService->getAuditTrailStatistics($startDate, $endDate);

        $logNames = Activity::distinct()->pluck('log_name')->filter()->sort()->values();
        $users = \App\Models\User::whereHas('activities')->with('activities')->get();

        return view('accounting.audit-trail.index', compact(
            'auditTrail', 'statistics', 'logNames', 'users', 'startDate', 'endDate', 'userId', 'logName'
        ));
    }

    /**
     * Show detailed audit trail for a specific activity
     */
    public function show(Activity $activity)
    {
        $activity->load(['causer', 'subject']);
        
        return view('accounting.audit-trail.show', compact('activity'));
    }

    /**
     * Get audit trail for a specific model
     */
    public function getModelTrail(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $modelClass = $request->model_type;
        $modelId = $request->model_id;

        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Invalid model type'], 400);
        }

        $model = $modelClass::find($modelId);
        if (!$model) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        $auditTrail = $this->auditTrailService->getModelAuditTrail($model);

        return response()->json($auditTrail);
    }

    /**
     * Get user activity summary
     */
    public function getUserActivity(Request $request, $userId)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $summary = $this->auditTrailService->getUserActivitySummary($userId, $startDate, $endDate);
        $user = \App\Models\User::findOrFail($userId);

        return view('accounting.audit-trail.user-activity', compact('summary', 'user', 'startDate', 'endDate'));
    }

    /**
     * Get compliance report
     */
    public function getComplianceReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $report = $this->auditTrailService->getComplianceReport($startDate, $endDate);

        return view('accounting.audit-trail.compliance-report', compact('report', 'startDate', 'endDate'));
    }

    /**
     * Export audit trail
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());
        $format = $request->get('format', 'csv');

        $data = $this->auditTrailService->exportAuditTrail($startDate, $endDate, $format);

        if ($format === 'csv') {
            $filename = "audit-trail-{$startDate}-to-{$endDate}.csv";
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, ['Timestamp', 'User', 'Action', 'Model Type', 'Model ID', 'Properties']);
                
                // Add data
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return response()->json($data);
    }

    /**
     * Get audit trail statistics for dashboard
     */
    public function getStatistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $statistics = $this->auditTrailService->getAuditTrailStatistics($startDate, $endDate);

        return response()->json($statistics);
    }

    /**
     * Get real-time audit trail updates
     */
    public function getRealTimeUpdates(Request $request)
    {
        $lastActivityId = $request->get('last_activity_id', 0);
        
        $newActivities = Activity::where('id', '>', $lastActivityId)
            ->with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'activities' => $newActivities,
            'last_activity_id' => $newActivities->max('id') ?: $lastActivityId,
        ]);
    }

    /**
     * Search audit trail
     */
    public function search(Request $request)
    {
        $query = $request->get('query');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $activities = Activity::whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($q) use ($query) {
                $q->where('description', 'like', "%{$query}%")
                  ->orWhere('properties', 'like', "%{$query}%");
            })
            ->with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('accounting.audit-trail.search-results', compact('activities', 'query', 'startDate', 'endDate'));
    }
}
