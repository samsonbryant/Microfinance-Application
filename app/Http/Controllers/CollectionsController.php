<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Client;
use App\Models\Branch;
use App\Models\Collection;
use App\Models\CommunicationLog;

class CollectionsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Loan::with(['client', 'branch'])
            ->where('status', 'overdue')
            ->when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        $overdueLoans = $query->orderBy('due_date', 'asc')->paginate(15);

        // Get collection statistics
        $stats = [
            'total_overdue' => $query->count(),
            'total_amount' => $query->sum('outstanding_balance'),
            'avg_days_overdue' => $query->get()->avg(function($loan) {
                return now()->diffInDays($loan->due_date);
            }),
            'collections_today' => Collection::whereDate('created_at', today())->count()
        ];

        return view('collections.index', compact('overdueLoans', 'stats'));
    }

    public function overdue()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Loan::with(['client', 'branch', 'collections'])
            ->where('status', 'overdue')
            ->when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        // Filter by days overdue
        if (request()->has('days_overdue')) {
            $days = request('days_overdue');
            $query->whereRaw("DATEDIFF(NOW(), due_date) >= ?", [$days]);
        }

        $overdueLoans = $query->orderBy('due_date', 'asc')->get();

        return view('collections.overdue', compact('overdueLoans'));
    }

    public function contact(Request $request, Loan $loan)
    {
        $request->validate([
            'contact_method' => 'required|in:phone,email,sms,visit',
            'message' => 'required|string|max:500',
            'follow_up_date' => 'nullable|date|after:today'
        ]);

        // Log the communication
        CommunicationLog::create([
            'client_id' => $loan->client_id,
            'loan_id' => $loan->id,
            'type' => $request->contact_method,
            'message' => $request->message,
            'created_by' => auth()->id(),
            'follow_up_date' => $request->follow_up_date
        ]);

        // Create collection record
        Collection::create([
            'loan_id' => $loan->id,
            'client_id' => $loan->client_id,
            'action_type' => 'contact',
            'method' => $request->contact_method,
            'notes' => $request->message,
            'created_by' => auth()->id(),
            'branch_id' => $loan->branch_id
        ]);

        return back()->with('success', 'Contact attempt recorded successfully.');
    }

    public function escalate(Request $request, Loan $loan)
    {
        $request->validate([
            'escalation_reason' => 'required|string|max:500',
            'escalation_level' => 'required|in:supervisor,manager,legal'
        ]);

        // Create collection record
        Collection::create([
            'loan_id' => $loan->id,
            'client_id' => $loan->client_id,
            'action_type' => 'escalation',
            'escalation_level' => $request->escalation_level,
            'notes' => $request->escalation_reason,
            'created_by' => auth()->id(),
            'branch_id' => $loan->branch_id
        ]);

        // Update loan status if escalated to legal
        if ($request->escalation_level === 'legal') {
            $loan->update(['status' => 'legal_action']);
        }

        return back()->with('success', 'Loan escalated successfully.');
    }
}
