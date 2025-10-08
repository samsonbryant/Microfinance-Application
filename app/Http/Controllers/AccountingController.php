<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedgerEntry;
use App\Models\JournalEntry;
use App\Models\ExpenseEntry;
use App\Services\AccountingService;
use App\Services\FinancialReportsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    protected $accountingService;
    protected $reportsService;

    public function __construct(AccountingService $accountingService, FinancialReportsService $reportsService)
    {
        $this->accountingService = $accountingService;
        $this->reportsService = $reportsService;
        
        $this->middleware('auth');
        $this->middleware('permission:view_accounting')->only(['index', 'chartOfAccounts', 'generalLedger', 'financialReports']);
        $this->middleware('permission:manage_chart_of_accounts')->only(['createAccount', 'storeAccount', 'editAccount', 'updateAccount', 'deleteAccount']);
        $this->middleware('permission:manage_journal_entries')->only(['journalEntries', 'createJournalEntry', 'storeJournalEntry', 'approveJournalEntry', 'postJournalEntry']);
        $this->middleware('permission:manage_expenses')->only(['expenseEntries', 'createExpenseEntry', 'storeExpenseEntry', 'approveExpenseEntry']);
    }

    /**
     * Accounting Dashboard
     */
    public function index()
    {
        $dashboardData = $this->reportsService->getDashboardData();
        
        // Get recent transactions
        $recentTransactions = GeneralLedgerEntry::with(['account', 'branch', 'user'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get pending approvals
        $pendingApprovals = collect();
        
        if (Auth::user()->can('approve_journal_entries')) {
            $pendingApprovals = $pendingApprovals->merge(
                JournalEntry::with(['user', 'branch'])
                    ->where('status', 'pending')
                    ->get()
            );
        }
        
        if (Auth::user()->can('approve_expenses')) {
            $pendingApprovals = $pendingApprovals->merge(
                ExpenseEntry::with(['user', 'branch', 'account'])
                    ->where('status', 'pending')
                    ->get()
            );
        }

        return view('accounting.dashboard', compact('dashboardData', 'recentTransactions', 'pendingApprovals'));
    }

    /**
     * Chart of Accounts Management
     */
    public function chartOfAccounts()
    {
        $accounts = ChartOfAccount::with('parent')
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        return view('accounting.chart-of-accounts.index', compact('accounts'));
    }

    /**
     * Create new chart of account
     */
    public function createAccount()
    {
        $accountTypes = [
            'asset' => 'Assets',
            'liability' => 'Liabilities',
            'equity' => 'Owner\'s Equity',
            'revenue' => 'Income',
            'expense' => 'Expenses'
        ];

        $categories = $this->getAccountCategories();
        $parentAccounts = ChartOfAccount::where('is_active', true)->get();

        return view('accounting.chart-of-accounts.create', compact('accountTypes', 'categories', 'parentAccounts'));
    }

    /**
     * Store new chart of account
     */
    public function storeAccount(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:chart_of_accounts,code|string|max:10',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
            'parent_id' => 'nullable|exists:chart_of_accounts,id'
        ]);

        $account = ChartOfAccount::create([
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'category' => $request->category,
            'description' => $request->description,
            'normal_balance' => ChartOfAccount::getNormalBalanceForType($request->type),
            'opening_balance' => $request->opening_balance ?? 0,
            'parent_id' => $request->parent_id,
            'is_active' => true,
            'is_system_account' => false
        ]);

        return redirect()->route('accounting.chart-of-accounts')
            ->with('success', 'Chart of account created successfully.');
    }

    /**
     * Edit chart of account
     */
    public function editAccount(ChartOfAccount $account)
    {
        $accountTypes = [
            'asset' => 'Assets',
            'liability' => 'Liabilities',
            'equity' => 'Owner\'s Equity',
            'revenue' => 'Income',
            'expense' => 'Expenses'
        ];

        $categories = $this->getAccountCategories();
        $parentAccounts = ChartOfAccount::where('is_active', true)
            ->where('id', '!=', $account->id)
            ->get();

        return view('accounting.chart-of-accounts.edit', compact('account', 'accountTypes', 'categories', 'parentAccounts'));
    }

    /**
     * Update chart of account
     */
    public function updateAccount(Request $request, ChartOfAccount $account)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:chart_of_accounts,code,' . $account->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_active' => 'boolean'
        ]);

        $account->update([
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'category' => $request->category,
            'description' => $request->description,
            'normal_balance' => ChartOfAccount::getNormalBalanceForType($request->type),
            'opening_balance' => $request->opening_balance ?? 0,
            'parent_id' => $request->parent_id,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('accounting.chart-of-accounts')
            ->with('success', 'Chart of account updated successfully.');
    }

    /**
     * Delete chart of account
     */
    public function deleteAccount(ChartOfAccount $account)
    {
        if (!$account->canBeDeleted()) {
            return redirect()->back()
                ->with('error', 'Cannot delete this account. It may have transactions or is a system account.');
        }

        $account->delete();

        return redirect()->route('accounting.chart-of-accounts')
            ->with('success', 'Chart of account deleted successfully.');
    }

    /**
     * General Ledger
     */
    public function generalLedger(Request $request)
    {
        $query = GeneralLedgerEntry::with(['account', 'branch', 'user'])
            ->where('status', 'approved');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        // Filter by account
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $entries = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();

        return view('accounting.general-ledger.index', compact('entries', 'accounts'));
    }

    /**
     * Financial Reports
     */
    public function financialReports(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());
        $basis = $request->get('basis', 'cash');

        $profitLoss = $this->reportsService->generateProfitAndLoss($startDate, $endDate, $basis);
        $balanceSheet = $this->reportsService->generateBalanceSheet($endDate);
        $trialBalance = $this->reportsService->generateTrialBalance($endDate);

        return view('accounting.financial-reports.index', compact(
            'profitLoss', 'balanceSheet', 'trialBalance', 'startDate', 'endDate', 'basis'
        ));
    }

    /**
     * Journal Entries Management
     */
    public function journalEntries()
    {
        $journalEntries = JournalEntry::with(['user', 'branch', 'approvedBy', 'lines.account'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('accounting.journal-entries.index', compact('journalEntries'));
    }

    /**
     * Create Journal Entry
     */
    public function createJournalEntry()
    {
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();

        return view('accounting.journal-entries.create', compact('accounts'));
    }

    /**
     * Store Journal Entry
     */
    public function storeJournalEntry(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'reference_number' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'required|string'
        ]);

        // Validate that debits equal credits
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($request->lines as $line) {
            $totalDebits += $line['debit'] ?? 0;
            $totalCredits += $line['credit'] ?? 0;
        }

        if (abs($totalDebits - $totalCredits) > 0.01) {
            return redirect()->back()
                ->withErrors(['lines' => 'Total debits must equal total credits.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $totalDebits, $totalCredits) {
            $journalEntry = JournalEntry::create([
                'journal_number' => JournalEntry::generateJournalNumber(),
                'branch_id' => Auth::user()->branch_id,
                'user_id' => Auth::id(),
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
                'reference_number' => $request->reference_number,
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'status' => 'pending'
            ]);

            foreach ($request->lines as $line) {
                $journalEntry->lines()->create([
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description']
                ]);
            }
        });

        return redirect()->route('accounting.journal-entries')
            ->with('success', 'Journal entry created successfully and is pending approval.');
    }

    /**
     * Approve Journal Entry
     */
    public function approveJournalEntry(JournalEntry $journalEntry)
    {
        $journalEntry->approve(Auth::id());

        return redirect()->back()
            ->with('success', 'Journal entry approved successfully.');
    }

    /**
     * Post Journal Entry
     */
    public function postJournalEntry(JournalEntry $journalEntry)
    {
        try {
            $journalEntry->post();

            return redirect()->back()
                ->with('success', 'Journal entry posted to general ledger successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error posting journal entry: ' . $e->getMessage());
        }
    }

    /**
     * Expense Entries Management
     */
    public function expenseEntries()
    {
        $expenseEntries = ExpenseEntry::with(['user', 'branch', 'account', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('accounting.expense-entries.index', compact('expenseEntries'));
    }

    /**
     * Create Expense Entry
     */
    public function createExpenseEntry()
    {
        $expenseAccounts = ChartOfAccount::where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('accounting.expense-entries.create', compact('expenseAccounts'));
    }

    /**
     * Store Expense Entry
     */
    public function storeExpenseEntry(Request $request)
    {
        $request->validate([
            'expense_date' => 'required|date',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reference_number' => 'nullable|string',
            'receipt_number' => 'nullable|string'
        ]);

        ExpenseEntry::create([
            'expense_number' => ExpenseEntry::generateExpenseNumber(),
            'branch_id' => Auth::user()->branch_id,
            'user_id' => Auth::id(),
            'account_id' => $request->account_id,
            'expense_date' => $request->expense_date,
            'amount' => $request->amount,
            'description' => $request->description,
            'reference_number' => $request->reference_number,
            'receipt_number' => $request->receipt_number,
            'status' => 'pending'
        ]);

        return redirect()->route('accounting.expense-entries')
            ->with('success', 'Expense entry created successfully and is pending approval.');
    }

    /**
     * Approve Expense Entry
     */
    public function approveExpenseEntry(ExpenseEntry $expenseEntry)
    {
        $expenseEntry->approve(Auth::id());

        return redirect()->back()
            ->with('success', 'Expense entry approved successfully.');
    }

    /**
     * Get account categories for dropdown
     */
    private function getAccountCategories()
    {
        return [
            'cash_on_hand' => 'Cash on Hand',
            'cash_in_bank' => 'Cash in Bank',
            'accounts_receivable' => 'Accounts Receivable',
            'loan_portfolio' => 'Loan Portfolio',
            'property_plant_equipment' => 'Property, Plant & Equipment',
            'accumulated_depreciation' => 'Accumulated Depreciation',
            'client_savings' => 'Client Savings',
            'interest_payable' => 'Interest Payable',
            'accounts_payable' => 'Accounts Payable',
            'loan_from_shareholders' => 'Loan from Shareholders',
            'capital' => 'Capital',
            'net_income' => 'Net Income',
            'retained_earnings' => 'Retained Earnings',
            'loan_interest_income' => 'Loan Interest Income',
            'penalty_income' => 'Penalty Income',
            'service_fees' => 'Service Fees',
            'other_income' => 'Other Income',
            'salaries_wages' => 'Salaries & Wages',
            'rent_expense' => 'Rent Expense',
            'communication_internet' => 'Communication & Internet',
            'legal_fees' => 'Legal Fees',
            'subscription_fees' => 'Subscription Fees',
            'utilities' => 'Utilities',
            'depreciation_expense' => 'Depreciation Expense',
            'loan_loss_expense' => 'Loan Loss Expense',
            'other_expenses' => 'Other Expenses'
        ];
    }
}