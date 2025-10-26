<?php

namespace App\Http\Controllers;

use App\Models\RecoveryAction;
use App\Models\Loan;
use Illuminate\Http\Request;

class RecoveryActionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = RecoveryAction::with(['collection.loan.client', 'performedBy']);
        
        // Filter by branch
        if (!$user->hasRole('admin') && $user->branch_id) {
            $query->whereHas('collection.loan', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }
        
        $actions = $query->orderBy('action_date', 'desc')->paginate(20);
        
        // Get statistics
        $stats = [
            'pending' => RecoveryAction::where('status', 'pending')->count(),
            'in_progress' => RecoveryAction::where('status', 'in_progress')->count(),
            'completed' => RecoveryAction::where('status', 'completed')->count(),
            'total_recovered' => RecoveryAction::where('status', 'completed')->sum('outcome') ?? 0,
        ];
        
        return view('recovery-actions.index', compact('actions', 'stats'));
    }

    public function create()
    {
        $loans = Loan::where('status', 'overdue')->with('client')->get();
        return view('recovery-actions.create', compact('loans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'action_type' => 'required|string',
            'action_date' => 'required|date',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'required|string',
            'expected_outcome' => 'nullable|string',
        ]);

        $validated['status'] = 'pending';

        RecoveryAction::create($validated);

        return redirect()->route('recovery-actions.index')
            ->with('success', 'Recovery action created successfully.');
    }

    public function show(RecoveryAction $recoveryAction)
    {
        $recoveryAction->load('loan.client', 'assignedTo');
        return view('recovery-actions.show', compact('recoveryAction'));
    }

    public function edit(RecoveryAction $recoveryAction)
    {
        return view('recovery-actions.edit', compact('recoveryAction'));
    }

    public function update(Request $request, RecoveryAction $recoveryAction)
    {
        $validated = $request->validate([
            'action_type' => 'required|string',
            'action_date' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'result' => 'nullable|string',
        ]);

        $recoveryAction->update($validated);

        return redirect()->route('recovery-actions.index')
            ->with('success', 'Recovery action updated successfully.');
    }

    public function destroy(RecoveryAction $recoveryAction)
    {
        $recoveryAction->delete();

        return redirect()->route('recovery-actions.index')
            ->with('success', 'Recovery action deleted successfully.');
    }
}

