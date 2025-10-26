<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientRiskProfile;
use App\Services\RiskAssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiskAssessmentController extends Controller
{
    protected $riskService;

    public function __construct(RiskAssessmentService $riskService)
    {
        $this->middleware('auth');
        $this->riskService = $riskService;
    }

    /**
     * Display risk assessment dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = ClientRiskProfile::with(['client', 'assessedBy']);
        
        // Filter by branch if not admin
        if (!$user->hasRole('admin') && $user->branch_id) {
            $query->whereHas('client', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }
        
        // Apply filters
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('client_number', 'LIKE', "%{$search}%");
            });
        }
        
        $profiles = $query->orderBy('risk_score', 'desc')->paginate(20);
        
        // Get summary statistics
        $stats = [
            'total_assessed' => ClientRiskProfile::count(),
            'low_risk' => ClientRiskProfile::where('risk_level', 'low')->count(),
            'medium_risk' => ClientRiskProfile::where('risk_level', 'medium')->count(),
            'high_risk' => ClientRiskProfile::where('risk_level', 'high')->count(),
            'very_high_risk' => ClientRiskProfile::where('risk_level', 'very_high')->count(),
        ];
        
        return view('risk-assessment.index', compact('profiles', 'stats'));
    }

    /**
     * Assess a client's risk
     */
    public function assess($clientId)
    {
        try {
            $result = $this->riskService->calculateRiskScore($clientId);
            
            // Log activity
            $client = Client::findOrFail($clientId);
            activity()
                ->performedOn($client)
                ->causedBy(auth()->user())
                ->log("Risk assessment completed - Score: {$result['risk_score']}%, Level: {$result['risk_level']}");
            
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Risk assessment completed successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Risk assessment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error assessing risk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show risk assessment details
     */
    public function show(ClientRiskProfile $riskProfile)
    {
        $riskProfile->load(['client.loans', 'assessedBy']);
        return view('risk-assessment.show', compact('riskProfile'));
    }

    /**
     * Reassess client risk
     */
    public function reassess(Request $request, $clientId)
    {
        try {
            DB::beginTransaction();
            
            $result = $this->riskService->calculateRiskScore($clientId);
            
            // Log activity
            $client = Client::findOrFail($clientId);
            activity()
                ->performedOn($client)
                ->causedBy(auth()->user())
                ->log("Risk reassessment completed - Score: {$result['risk_score']}%, Level: {$result['risk_level']}");
            
            DB::commit();
            
            return redirect()->route('risk-assessment.index')
                ->with('success', 'Client risk reassessed successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Risk reassessment error: ' . $e->getMessage());
            return back()->with('error', 'Error reassessing risk: ' . $e->getMessage());
        }
    }

    /**
     * Get clients pending assessment
     */
    public function pending()
    {
        $user = auth()->user();
        
        $clients = Client::doesntHave('riskProfile')
            ->when(!$user->hasRole('admin') && $user->branch_id, function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->where('status', 'active')
            ->with('loans')
            ->paginate(20);
        
        return view('risk-assessment.pending', compact('clients'));
    }

    /**
     * Batch assess multiple clients
     */
    public function batchAssess(Request $request)
    {
        $validated = $request->validate([
            'client_ids' => 'required|array',
            'client_ids.*' => 'exists:clients,id'
        ]);

        try {
            DB::beginTransaction();
            
            $results = [];
            foreach ($validated['client_ids'] as $clientId) {
                $results[] = $this->riskService->calculateRiskScore($clientId);
            }
            
            DB::commit();
            
            return redirect()->route('risk-assessment.index')
                ->with('success', count($validated['client_ids']) . ' clients assessed successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Batch risk assessment error: ' . $e->getMessage());
            return back()->with('error', 'Error in batch assessment: ' . $e->getMessage());
        }
    }
}

