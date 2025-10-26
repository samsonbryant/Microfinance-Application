<?php

namespace App\Http\Controllers;

use App\Models\Reconciliation;
use App\Models\ChartOfAccount;
use App\Services\ReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReconciliationController extends Controller
{
    protected $reconciliationService;

    public function __construct(ReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
        
        $this->middleware('auth');
        $this->middleware('permission:view_reconciliations')->only(['index', 'show']);
        $this->middleware('permission:manage_reconciliations')->only(['create', 'store', 'start', 'complete', 'approve']);
    }

    /**
     * Display reconciliations list
     */
    public function index(Request $request)
    {
        $query = Reconciliation::with(['account', 'branch', 'user', 'approvedBy']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('reconciliation_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('reconciliation_date', '<=', $request->end_date);
        }

        $reconciliations = $query->orderBy('reconciliation_date', 'desc')
            ->paginate(20);

        $reconciliationTypes = [
            'cash' => 'Cash Reconciliation',
            'bank' => 'Bank Reconciliation',
            'loan_portfolio' => 'Loan Portfolio Reconciliation',
            'savings_portfolio' => 'Savings Portfolio Reconciliation',
        ];

        return view('accounting.reconciliations.index', compact('reconciliations', 'reconciliationTypes'));
    }

    /**
     * Show reconciliation details
     */
    public function show(Reconciliation $reconciliation)
    {
        $reconciliation->load(['account', 'branch', 'user', 'approvedBy', 'items']);
        
        $summary = $this->reconciliationService->getReconciliationSummary($reconciliation->id);
        
        $matchedItems = $reconciliation->getMatchedItems();
        $unmatchedItems = $reconciliation->getUnmatchedItems();
        $disputedItems = $reconciliation->getDisputedItems();

        return view('accounting.reconciliations.show', compact(
            'reconciliation', 'summary', 'matchedItems', 'unmatchedItems', 'disputedItems'
        ));
    }

    /**
     * Show create reconciliation form
     */
    public function create()
    {
        $accounts = ChartOfAccount::where('is_active', true)
            ->whereIn('type', ['asset', 'liability'])
            ->orderBy('code')
            ->get();

        $reconciliationTypes = [
            'cash' => 'Cash Reconciliation',
            'bank' => 'Bank Reconciliation',
            'loan_portfolio' => 'Loan Portfolio Reconciliation',
            'savings_portfolio' => 'Savings Portfolio Reconciliation',
        ];

        return view('accounting.reconciliations.create', compact('accounts', 'reconciliationTypes'));
    }

    /**
     * Store new reconciliation
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:cash,bank,loan_portfolio,savings_portfolio',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'reconciliation_date' => 'required|date',
            'actual_balance' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $reconciliation = $this->reconciliationService->createReconciliation(
            $request->type,
            $request->account_id,
            Auth::user()->branch_id,
            Auth::id(),
            $request->reconciliation_date,
            $request->actual_balance,
            $request->notes
        );

        return redirect()->route('accounting.reconciliations.show', $reconciliation)
            ->with('success', 'Reconciliation created successfully.');
    }

    /**
     * Start reconciliation process
     */
    public function start(Reconciliation $reconciliation)
    {
        if (!$reconciliation->isDraft()) {
            return redirect()->back()
                ->with('error', 'Only draft reconciliations can be started.');
        }

        $this->reconciliationService->startReconciliation($reconciliation->id);

        return redirect()->back()
            ->with('success', 'Reconciliation process started. System transactions have been loaded.');
    }

    /**
     * Import bank statement
     */
    public function importBankStatement(Request $request, Reconciliation $reconciliation)
    {
        $request->validate([
            'statement_data' => 'required|array',
            'statement_data.*.date' => 'required|date',
            'statement_data.*.description' => 'required|string',
            'statement_data.*.amount' => 'required|numeric',
            'statement_data.*.reference' => 'nullable|string',
        ]);

        $this->reconciliationService->importBankStatement($reconciliation->id, $request->statement_data);

        return redirect()->back()
            ->with('success', 'Bank statement imported successfully.');
    }

    /**
     * Auto-match items
     */
    public function autoMatch(Reconciliation $reconciliation)
    {
        $matched = $this->reconciliationService->autoMatchItems($reconciliation->id);

        return redirect()->back()
            ->with('success', "Auto-matched {$matched} items.");
    }

    /**
     * Match specific items
     */
    public function matchItems(Request $request, Reconciliation $reconciliation)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:reconciliation_items,id',
        ]);

        foreach ($request->item_ids as $itemId) {
            $item = $reconciliation->items()->findOrFail($itemId);
            $item->match();
        }

        return redirect()->back()
            ->with('success', 'Items matched successfully.');
    }

    /**
     * Dispute items
     */
    public function disputeItems(Request $request, Reconciliation $reconciliation)
    {
        $request->validate([
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:reconciliation_items,id',
            'notes' => 'required|string',
        ]);

        foreach ($request->item_ids as $itemId) {
            $item = $reconciliation->items()->findOrFail($itemId);
            $item->dispute($request->notes);
        }

        return redirect()->back()
            ->with('success', 'Items marked as disputed.');
    }

    /**
     * Complete reconciliation
     */
    public function complete(Reconciliation $reconciliation)
    {
        try {
            $this->reconciliationService->completeReconciliation($reconciliation->id);

            return redirect()->back()
                ->with('success', 'Reconciliation completed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Approve reconciliation
     */
    public function approve(Reconciliation $reconciliation)
    {
        if (!$reconciliation->isCompleted()) {
            return redirect()->back()
                ->with('error', 'Only completed reconciliations can be approved.');
        }

        $reconciliation->approve(Auth::id());

        return redirect()->back()
            ->with('success', 'Reconciliation approved successfully.');
    }

    /**
     * Create adjustment entries
     */
    public function createAdjustments(Request $request, Reconciliation $reconciliation)
    {
        $request->validate([
            'adjustments' => 'required|array',
            'adjustments.*.amount' => 'required|numeric',
            'adjustments.*.description' => 'required|string',
            'adjustments.*.adjustment_account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        try {
            $this->reconciliationService->createAdjustmentEntries($reconciliation->id, $request->adjustments);

            return redirect()->back()
                ->with('success', 'Adjustment entries created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get reconciliation history for an account
     */
    public function getAccountHistory($accountId)
    {
        $history = $this->reconciliationService->getReconciliationHistory($accountId);

        return response()->json($history);
    }

    /**
     * Get overdue reconciliations
     */
    public function getOverdueReconciliations()
    {
        $overdue = $this->reconciliationService->getOverdueReconciliations();

        return response()->json($overdue);
    }

    /**
     * Generate reconciliation report
     */
    public function generateReport(Reconciliation $reconciliation)
    {
        $report = $this->reconciliationService->generateReconciliationReport($reconciliation->id);

        return response()->json($report);
    }
}
