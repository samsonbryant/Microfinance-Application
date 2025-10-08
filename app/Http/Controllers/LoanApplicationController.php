<?php

namespace App\Http\Controllers;

use App\Models\LoanApplication;
use App\Models\Client;
use App\Models\Branch;
use Illuminate\Http\Request;

class LoanApplicationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = LoanApplication::with(['client', 'branch', 'createdBy'])
            ->when($branchId && !$user->hasRole('admin'), function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        // Filter by status
        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('loan-applications.index', compact('applications'));
    }

    public function create()
    {
        $user = auth()->user();
        $clients = Client::where('status', 'active')->get();
        $branches = $user->hasRole('admin') ? Branch::all() : collect([$user->branch]);

        return view('loan-applications.create', compact('clients', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'branch_id' => 'required|exists:branches,id',
            'loan_type' => 'required|string',
            'requested_amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'purpose' => 'required|string',
            'employment_status' => 'nullable|string',
            'monthly_income' => 'nullable|numeric|min:0',
            'collateral_type' => 'nullable|string',
            'collateral_value' => 'nullable|numeric|min:0',
        ]);

        // Map form fields to database columns
        $data = [
            'client_id' => $validated['client_id'],
            'branch_id' => $validated['branch_id'],
            'loan_type' => $validated['loan_type'],
            'requested_amount' => $validated['requested_amount'],
            'requested_term_months' => $validated['term_months'],
            'term_months' => $validated['term_months'], // Also set the alias
            'requested_interest_rate' => $validated['interest_rate'],
            'interest_rate' => $validated['interest_rate'], // Also set the alias
            'loan_purpose' => $validated['purpose'],
            'purpose' => $validated['purpose'], // Also set the alias
            'employment_status' => $validated['employment_status'] ?? null,
            'monthly_income' => $validated['monthly_income'] ?? null,
            'collateral_type' => $validated['collateral_type'] ?? null,
            'collateral_value' => $validated['collateral_value'] ?? null,
            'application_number' => $this->generateApplicationNumber(),
            'status' => 'pending',
            'created_by' => auth()->id(),
            'loan_officer_id' => null, // Will be assigned later
            'payment_frequency' => 'monthly', // Default value
        ];

        LoanApplication::create($data);

        return redirect()->route('loan-applications.index')
            ->with('success', 'Loan application submitted successfully.');
    }

    public function show(LoanApplication $loanApplication)
    {
        $loanApplication->load(['client', 'branch', 'createdBy']);
        return view('loan-applications.show', compact('loanApplication'));
    }

    public function edit(LoanApplication $loanApplication)
    {
        $clients = Client::where('status', 'active')->get();
        $branches = Branch::all();
        return view('loan-applications.edit', compact('loanApplication', 'clients', 'branches'));
    }

    public function update(Request $request, LoanApplication $loanApplication)
    {
        $validated = $request->validate([
            'requested_amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'purpose' => 'required|string',
            'status' => 'required|in:pending,approved,rejected,cancelled',
            'rejection_reason' => 'nullable|string',
        ]);

        // Map form fields to database columns
        $data = [
            'requested_amount' => $validated['requested_amount'],
            'requested_term_months' => $validated['term_months'],
            'term_months' => $validated['term_months'],
            'requested_interest_rate' => $validated['interest_rate'],
            'interest_rate' => $validated['interest_rate'],
            'loan_purpose' => $validated['purpose'],
            'purpose' => $validated['purpose'],
            'status' => $validated['status'],
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ];

        $loanApplication->update($data);

        return redirect()->route('loan-applications.index')
            ->with('success', 'Loan application updated successfully.');
    }

    public function destroy(LoanApplication $loanApplication)
    {
        $loanApplication->delete();

        return redirect()->route('loan-applications.index')
            ->with('success', 'Loan application deleted successfully.');
    }

    private function generateApplicationNumber()
    {
        $prefix = 'APP';
        $timestamp = now()->format('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }
}
