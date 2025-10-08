<?php

namespace App\Http\Controllers;

use App\Models\Collateral;
use App\Models\Loan;
use Illuminate\Http\Request;

class CollateralController extends Controller
{
    public function index()
    {
        $collaterals = Collateral::with('loan.client')->latest()->paginate(20);
        return view('collaterals.index', compact('collaterals'));
    }

    public function create()
    {
        $loans = Loan::whereIn('status', ['pending', 'approved'])->with('client')->get();
        return view('collaterals.create', compact('loans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'type' => 'required|string',
            'description' => 'required|string',
            'estimated_value' => 'required|numeric|min:0',
            'location' => 'nullable|string',
            'ownership_proof' => 'nullable|string',
            'condition' => 'nullable|string',
        ]);

        $validated['status'] = 'submitted';

        Collateral::create($validated);

        return redirect()->route('collaterals.index')
            ->with('success', 'Collateral created successfully.');
    }

    public function show(Collateral $collateral)
    {
        $collateral->load('loan.client');
        return view('collaterals.show', compact('collateral'));
    }

    public function edit(Collateral $collateral)
    {
        $loans = Loan::with('client')->get();
        return view('collaterals.edit', compact('collateral', 'loans'));
    }

    public function update(Request $request, Collateral $collateral)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'description' => 'required|string',
            'estimated_value' => 'required|numeric|min:0',
            'location' => 'nullable|string',
            'ownership_proof' => 'nullable|string',
            'condition' => 'nullable|string',
            'status' => 'required|in:submitted,verified,rejected',
        ]);

        $collateral->update($validated);

        return redirect()->route('collaterals.index')
            ->with('success', 'Collateral updated successfully.');
    }

    public function destroy(Collateral $collateral)
    {
        $collateral->delete();

        return redirect()->route('collaterals.index')
            ->with('success', 'Collateral deleted successfully.');
    }
}

