<?php

namespace App\Http\Controllers;

use App\Models\ClientRiskProfile;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientRiskProfileController extends Controller
{
    public function index()
    {
        $profiles = ClientRiskProfile::with('client')->latest()->paginate(20);
        return view('client-risk-profiles.index', compact('profiles'));
    }

    public function create()
    {
        $clients = Client::doesntHave('riskProfile')->get();
        return view('client-risk-profiles.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id|unique:client_risk_profiles',
            'credit_score' => 'required|integer|min:0|max:1000',
            'risk_category' => 'required|in:low,medium,high',
            'income_stability' => 'required|in:stable,moderate,unstable',
            'employment_status' => 'required|string',
            'debt_to_income_ratio' => 'nullable|numeric|min:0',
            'payment_history' => 'nullable|string',
            'assessment_notes' => 'nullable|string',
        ]);

        ClientRiskProfile::create($validated);

        return redirect()->route('client-risk-profiles.index')
            ->with('success', 'Client risk profile created successfully.');
    }

    public function show(ClientRiskProfile $clientRiskProfile)
    {
        $clientRiskProfile->load('client');
        return view('client-risk-profiles.show', compact('clientRiskProfile'));
    }

    public function edit(ClientRiskProfile $clientRiskProfile)
    {
        return view('client-risk-profiles.edit', compact('clientRiskProfile'));
    }

    public function update(Request $request, ClientRiskProfile $clientRiskProfile)
    {
        $validated = $request->validate([
            'credit_score' => 'required|integer|min:0|max:1000',
            'risk_category' => 'required|in:low,medium,high',
            'income_stability' => 'required|in:stable,moderate,unstable',
            'employment_status' => 'required|string',
            'debt_to_income_ratio' => 'nullable|numeric|min:0',
            'payment_history' => 'nullable|string',
            'assessment_notes' => 'nullable|string',
        ]);

        $clientRiskProfile->update($validated);

        return redirect()->route('client-risk-profiles.index')
            ->with('success', 'Client risk profile updated successfully.');
    }

    public function destroy(ClientRiskProfile $clientRiskProfile)
    {
        $clientRiskProfile->delete();

        return redirect()->route('client-risk-profiles.index')
            ->with('success', 'Client risk profile deleted successfully.');
    }
}

