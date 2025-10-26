<?php

namespace App\Http\Controllers;

use App\Models\CommunicationLog;
use App\Models\Client;
use Illuminate\Http\Request;

class CommunicationLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = CommunicationLog::with(['client', 'sentBy']);
        
        // Filter by branch
        if (!$user->hasRole('admin') && $user->branch_id) {
            $query->whereHas('client', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }
        
        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        
        $logs = $query->orderBy('sent_at', 'desc')->paginate(20);
        
        // Get statistics
        $stats = [
            'total_communications' => CommunicationLog::count(),
            'today' => CommunicationLog::whereDate('sent_at', today())->count(),
            'this_week' => CommunicationLog::whereBetween('sent_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => CommunicationLog::whereMonth('sent_at', now()->month)->count(),
        ];
        
        $clients = Client::orderBy('first_name')->get();
        
        return view('communication-logs.index', compact('logs', 'stats', 'clients'));
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

