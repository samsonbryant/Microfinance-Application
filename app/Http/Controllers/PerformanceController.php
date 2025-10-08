<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use App\Models\User;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index()
    {
        $performances = Performance::with('user', 'evaluator')->latest()->paginate(20);
        return view('performance.index', compact('performances'));
    }

    public function create()
    {
        $staff = User::whereHas('roles')->get();
        return view('performance.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'period' => 'required|string',
            'rating' => 'required|numeric|min:1|max:5',
            'loans_disbursed' => 'nullable|integer',
            'collections_made' => 'nullable|numeric',
            'clients_acquired' => 'nullable|integer',
            'targets_achieved' => 'nullable|numeric',
            'strengths' => 'nullable|string',
            'weaknesses' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        $validated['evaluator_id'] = auth()->id();
        $validated['evaluation_date'] = now();

        Performance::create($validated);

        return redirect()->route('performance.index')
            ->with('success', 'Performance evaluation created successfully.');
    }

    public function show(Performance $performance)
    {
        $performance->load('user', 'evaluator');
        return view('performance.show', compact('performance'));
    }

    public function edit(Performance $performance)
    {
        return view('performance.edit', compact('performance'));
    }

    public function update(Request $request, Performance $performance)
    {
        $validated = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'loans_disbursed' => 'nullable|integer',
            'collections_made' => 'nullable|numeric',
            'clients_acquired' => 'nullable|integer',
            'targets_achieved' => 'nullable|numeric',
            'strengths' => 'nullable|string',
            'weaknesses' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        $performance->update($validated);

        return redirect()->route('performance.index')
            ->with('success', 'Performance evaluation updated successfully.');
    }

    public function destroy(Performance $performance)
    {
        $performance->delete();

        return redirect()->route('performance.index')
            ->with('success', 'Performance evaluation deleted successfully.');
    }
}

