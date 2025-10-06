<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['users', 'clients', 'loans'])
            ->withSum('loans', 'outstanding_balance')
            ->paginate(15);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'manager_name' => 'required|string|max:255',
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $branch->load(['users', 'clients', 'loans', 'savingsAccounts']);
        
        $stats = [
            'total_users' => $branch->users()->count(),
            'total_clients' => $branch->clients()->count(),
            'active_loans' => $branch->loans()->where('status', 'active')->count(),
            'total_portfolio' => $branch->loans()->whereIn('status', ['active', 'overdue'])->sum('outstanding_balance'),
            'total_savings' => $branch->savingsAccounts()->where('status', 'active')->sum('balance'),
        ];

        return view('branches.show', compact('branch', 'stats'));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:branches,code,' . $branch->id,
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'manager_name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $branch->update($request->all());

        return redirect()->route('branches.show', $branch)
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        // Check if branch has associated data
        if ($branch->users()->count() > 0 || $branch->clients()->count() > 0) {
            return back()->with('error', 'Cannot delete branch with associated users or clients.');
        }

        $branch->delete();
        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
}
