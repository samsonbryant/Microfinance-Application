<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\RecoveryAction;
use App\Models\Collection;

class RecoveryController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Loan::with(['client', 'branch', 'recoveryActions'])
            ->whereIn('status', ['overdue', 'legal_action', 'written_off'])
            ->when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        $recoveryLoans = $query->orderBy('due_date', 'asc')->paginate(15);

        // Get recovery statistics
        $stats = [
            'total_recovery' => $query->count(),
            'total_amount' => $query->sum('outstanding_balance'),
            'legal_actions' => $query->where('status', 'legal_action')->count(),
            'written_off' => $query->where('status', 'written_off')->count()
        ];

        return view('recovery.index', compact('recoveryLoans', 'stats'));
    }

    public function actions()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = RecoveryAction::with(['loan.client', 'loan.branch', 'createdBy'])
            ->when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->whereHas('loan', function($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            });

        $recoveryActions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('recovery.actions', compact('recoveryActions'));
    }

    public function legalAction(Request $request, Loan $loan)
    {
        $request->validate([
            'action_type' => 'required|in:demand_letter,legal_notice,court_filing',
            'description' => 'required|string|max:1000',
            'expected_outcome' => 'required|string|max:500',
            'estimated_cost' => 'nullable|numeric|min:0'
        ]);

        // Create recovery action
        RecoveryAction::create([
            'loan_id' => $loan->id,
            'action_type' => $request->action_type,
            'description' => $request->description,
            'expected_outcome' => $request->expected_outcome,
            'estimated_cost' => $request->estimated_cost,
            'status' => 'initiated',
            'created_by' => auth()->id(),
            'branch_id' => $loan->branch_id
        ]);

        // Update loan status
        $loan->update(['status' => 'legal_action']);

        return back()->with('success', 'Legal action initiated successfully.');
    }

    public function collateralSeizure(Request $request, Loan $loan)
    {
        $request->validate([
            'seizure_reason' => 'required|string|max:1000',
            'collateral_value' => 'required|numeric|min:0',
            'seizure_date' => 'required|date|after_or_equal:today',
            'storage_location' => 'required|string|max:255'
        ]);

        // Create recovery action
        RecoveryAction::create([
            'loan_id' => $loan->id,
            'action_type' => 'collateral_seizure',
            'description' => $request->seizure_reason,
            'expected_outcome' => 'Collateral seized and stored',
            'estimated_cost' => 0,
            'status' => 'initiated',
            'created_by' => auth()->id(),
            'branch_id' => $loan->branch_id,
            'additional_data' => [
                'collateral_value' => $request->collateral_value,
                'seizure_date' => $request->seizure_date,
                'storage_location' => $request->storage_location
            ]
        ]);

        return back()->with('success', 'Collateral seizure action initiated successfully.');
    }
}
