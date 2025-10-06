<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralLedger;
use App\Models\ChartOfAccount;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;

class GeneralLedgerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|general_manager|branch_manager');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GeneralLedger::with(['account', 'createdBy', 'reference']);

        // Filter by account
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $ledgerEntries = $query->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $accounts = ChartOfAccount::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('general-ledger.index', compact('ledgerEntries', 'accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = ChartOfAccount::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('general-ledger.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:chart_of_accounts,id',
            'debit' => 'required_without:credit|numeric|min:0',
            'credit' => 'required_without:debit|numeric|min:0',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ]);

        // Ensure only one of debit or credit is provided
        if ($request->debit && $request->credit) {
            return back()->withErrors(['debit' => 'Please provide either debit or credit, not both.']);
        }

        if (!$request->debit && !$request->credit) {
            return back()->withErrors(['debit' => 'Please provide either debit or credit amount.']);
        }

        GeneralLedger::create([
            'account_id' => $request->account_id,
            'debit' => $request->debit ?? 0,
            'credit' => $request->credit ?? 0,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'reference_type' => $request->reference_type,
            'reference_id' => $request->reference_id,
            'created_by' => auth()->id(),
            'branch_id' => auth()->user()->branch_id,
        ]);

        return redirect()->route('general-ledger.index')
            ->with('success', 'Ledger entry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GeneralLedger $generalLedger)
    {
        $generalLedger->load(['account', 'createdBy', 'reference']);
        
        return view('general-ledger.show', compact('generalLedger'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GeneralLedger $generalLedger)
    {
        $accounts = ChartOfAccount::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('general-ledger.edit', compact('generalLedger', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GeneralLedger $generalLedger)
    {
        $request->validate([
            'account_id' => 'required|exists:chart_of_accounts,id',
            'debit' => 'required_without:credit|numeric|min:0',
            'credit' => 'required_without:debit|numeric|min:0',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ]);

        // Ensure only one of debit or credit is provided
        if ($request->debit && $request->credit) {
            return back()->withErrors(['debit' => 'Please provide either debit or credit, not both.']);
        }

        if (!$request->debit && !$request->credit) {
            return back()->withErrors(['debit' => 'Please provide either debit or credit amount.']);
        }

        $generalLedger->update([
            'account_id' => $request->account_id,
            'debit' => $request->debit ?? 0,
            'credit' => $request->credit ?? 0,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'reference_type' => $request->reference_type,
            'reference_id' => $request->reference_id,
        ]);

        return redirect()->route('general-ledger.index')
            ->with('success', 'Ledger entry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GeneralLedger $generalLedger)
    {
        $generalLedger->delete();

        return redirect()->route('general-ledger.index')
            ->with('success', 'Ledger entry deleted successfully.');
    }

    /**
     * Get trial balance
     */
    public function trialBalance()
    {
        $accountingService = app(AccountingService::class);
        $trialBalance = $accountingService->getTrialBalance();

        return view('general-ledger.trial-balance', compact('trialBalance'));
    }

    /**
     * Get profit and loss statement
     */
    public function profitAndLoss(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now()->endOfYear());

        $accountingService = app(AccountingService::class);
        $profitLoss = $accountingService->getProfitAndLoss($startDate, $endDate);

        return view('general-ledger.profit-loss', compact('profitLoss', 'startDate', 'endDate'));
    }

    /**
     * Get balance sheet
     */
    public function balanceSheet()
    {
        $accountingService = app(AccountingService::class);
        $balanceSheet = $accountingService->getBalanceSheet();

        return view('general-ledger.balance-sheet', compact('balanceSheet'));
    }
}
