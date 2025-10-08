<?php

namespace App\Http\Controllers;

use App\Models\ApprovalWorkflow;
use App\Models\Loan;
use Illuminate\Http\Request;

class ApprovalWorkflowController extends Controller
{
    public function index()
    {
        $workflows = ApprovalWorkflow::with('loan.client', 'approver')->latest()->paginate(20);
        return view('approval-workflows.index', compact('workflows'));
    }

    public function create()
    {
        $loans = Loan::where('status', 'pending')->with('client')->get();
        return view('approval-workflows.create', compact('loans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'level' => 'required|integer|min:1',
            'approver_id' => 'required|exists:users,id',
            'status' => 'required|in:pending,approved,rejected',
            'comments' => 'nullable|string',
        ]);

        ApprovalWorkflow::create($validated);

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
            'comments' => 'nullable|string',
        ]);

        $approvalWorkflow->update($validated);

        return redirect()->route('approval-workflows.index')
            ->with('success', 'Approval workflow updated successfully.');
    }

    public function destroy(ApprovalWorkflow $approvalWorkflow)
    {
        $approvalWorkflow->delete();

        return redirect()->route('approval-workflows.index')
            ->with('success', 'Approval workflow deleted successfully.');
    }
}

