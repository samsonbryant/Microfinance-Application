<?php

namespace App\Http\Controllers;

use App\Models\CommunicationLog;
use App\Models\Client;
use Illuminate\Http\Request;

class CommunicationLogController extends Controller
{
    public function index()
    {
        $logs = CommunicationLog::with('client', 'user')->latest()->paginate(20);
        return view('communication-logs.index', compact('logs'));
    }

    public function create()
    {
        $clients = Client::all();
        return view('communication-logs.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'communication_type' => 'required|in:call,sms,email,visit,letter',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'response' => 'nullable|string',
            'outcome' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['communication_date'] = now();

        CommunicationLog::create($validated);

        return redirect()->route('communication-logs.index')
            ->with('success', 'Communication log created successfully.');
    }

    public function show(CommunicationLog $communicationLog)
    {
        $communicationLog->load('client', 'user');
        return view('communication-logs.show', compact('communicationLog'));
    }

    public function edit(CommunicationLog $communicationLog)
    {
        return view('communication-logs.edit', compact('communicationLog'));
    }

    public function update(Request $request, CommunicationLog $communicationLog)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'response' => 'nullable|string',
            'outcome' => 'nullable|string',
        ]);

        $communicationLog->update($validated);

        return redirect()->route('communication-logs.index')
            ->with('success', 'Communication log updated successfully.');
    }

    public function destroy(CommunicationLog $communicationLog)
    {
        $communicationLog->delete();

        return redirect()->route('communication-logs.index')
            ->with('success', 'Communication log deleted successfully.');
    }
}

