<?php

namespace App\Http\Controllers;

use App\Models\ApprovalWorkflow;
use App\Models\Loan;
use Illuminate\Http\Request;

class ApprovalWorkflowController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = ApprovalWorkflow::with(['loan.client', 'approver']);
        
        // Filter by user role
        if ($user->hasRole('admin')) {
            // Admins see all workflows
        } elseif ($user->hasRole('branch_manager')) {
            // Branch managers see their branch workflows
            $query->whereHas('loan', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        } else {
            // Loan officers see only their assigned approvals
            $query->where('approver_id', $user->id);
        }
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        
        $workflows = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get statistics
        $stats = [
            'pending' => ApprovalWorkflow::where('status', 'pending')->count(),
            'approved' => ApprovalWorkflow::where('status', 'approved')->count(),
            'rejected' => ApprovalWorkflow::where('status', 'rejected')->count(),
        ];
        
        return view('approval-workflows.index', compact('workflows', 'stats'));
    }

    public function create()
    {
        $loans = Loan::where('status', 'pending')->with('client')->get();
        $users = \App\Models\User::whereIn('role', ['admin', 'branch_manager', 'loan_officer'])->get();
        return view('approval-workflows.create', compact('loans', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'level' => 'required|integer|min:1|max:5',
            'approver_id' => 'required|exists:users,id',
            'status' => 'in:pending,approved,rejected',
            'comments' => 'nullable|string|max:1000',
        ]);

        $validated['status'] = $validated['status'] ?? 'pending';
        $validated['created_by'] = auth()->id();
        
        // Generate workflow name
        $loan = Loan::findOrFail($validated['loan_id']);
        $validated['workflow_name'] = "Loan {$loan->loan_number} - Level {$validated['level']} Approval";
        $validated['workflow_type'] = 'loan_application';

        $workflow = ApprovalWorkflow::create($validated);
        
        // Log activity
        activity()
            ->performedOn($workflow)
            ->causedBy(auth()->user())
            ->log("Approval workflow created for loan: {$workflow->loan->loan_number}");

        return redirect()->route('approval-workflows.index')
            ->with('success', 'Approval workflow created successfully.');
    }

    public function show(ApprovalWorkflow $approvalWorkflow)
    {
        $approvalWorkflow->load('loan.client', 'approver');
        return view('approval-workflows.show', compact('approvalWorkflow'));
    }

    public function edit(ApprovalWorkflow $approvalWorkflow)
    {
        return view('approval-workflows.edit', compact('approvalWorkflow'));
    }

    public function update(Request $request, ApprovalWorkflow $approvalWorkflow)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'comments' => 'nullable|string|max:1000',
        ]);

        $validated['reviewed_by'] = auth()->id();
        $validated['reviewed_at'] = now();

        $approvalWorkflow->update($validated);
        
        // Update loan if all approvals are complete
        if ($validated['status'] === 'approved') {
            $this->checkAndUpdateLoanStatus($approvalWorkflow->loan_id);
        }
        
        // Log activity
        activity()
            ->performedOn($approvalWorkflow)
            ->causedBy(auth()->user())
            ->log("Approval workflow updated to: {$validated['status']}");

        return redirect()->route('approval-workflows.index')
            ->with('success', 'Approval workflow updated successfully.');
    }
    
    /**
     * Check if all required approvals are complete and update loan status
     */
    private function checkAndUpdateLoanStatus($loanId)
    {
        $loan = Loan::findOrFail($loanId);
        $workflows = ApprovalWorkflow::where('loan_id', $loanId)->get();
        
        // Check if all workflows are approved
        if ($workflows->every(fn($w) => $w->status === 'approved')) {
            $loan->update(['status' => 'approved', 'approved_at' => now()]);
        }
    }

    public function destroy(ApprovalWorkflow $approvalWorkflow)
    {
        $approvalWorkflow->delete();

        return redirect()->route('approval-workflows.index')
            ->with('success', 'Approval workflow deleted successfully.');
    }
}

