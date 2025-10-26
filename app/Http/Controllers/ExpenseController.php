<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ChartOfAccount;
use App\Models\Bank;
use App\Models\Branch;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage_expenses')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $expenses = Expense::with(['account', 'bank', 'branch', 'user'])
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->from_date, function ($query, $date) {
                    return $query->whereDate('transaction_date', '>=', $date);
                })
                ->when($request->to_date, function ($query, $date) {
                    return $query->whereDate('transaction_date', '<=', $date);
                })
                ->latest();

            return DataTables::of($expenses)
                ->addColumn('date', function ($expense) {
                    return $expense->transaction_date->format('Y-m-d');
                })
                ->addColumn('account_name', function ($expense) {
                    return $expense->account->name ?? 'N/A';
                })
                ->addColumn('amount', function ($expense) {
                    return '$' . number_format($expense->amount, 2);
                })
                ->addColumn('status_badge', function ($expense) {
                    return '<span class="badge bg-' . $expense->getStatusBadgeClass() . '">' . ucfirst($expense->status) . '</span>';
                })
                ->addColumn('action', function ($expense) {
                    $showUrl = route('accounting.expenses.show', $expense);
                    $approveUrl = route('accounting.expenses.approve', $expense);
                    $postUrl = route('accounting.expenses.post', $expense);

                    $buttons = '<div class="btn-group" role="group">';
                    $buttons .= '<a href="' . $showUrl . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';

                    if ($expense->canBeApproved() && auth()->user()->can('approve_expenses')) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-success" onclick="approveExpense(' . $expense->id . ')"><i class="fas fa-check"></i></button>';
                    }

                    if ($expense->canBePosted() && auth()->user()->can('post_expenses')) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-primary" onclick="postExpense(' . $expense->id . ')"><i class="fas fa-paper-plane"></i></button>';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('accounting.expenses.index');
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('type', 'expense')
            ->where('is_active', true)
            ->get();
        $banks = Bank::where('is_active', true)->get();
        $branches = Branch::all();

        return view('accounting.expenses.create', compact('accounts', 'banks', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,mobile_money',
            'bank_id' => 'required_if:payment_method,cheque,bank_transfer|nullable|exists:banks,id',
            'reference_number' => 'nullable|string|max:255',
            'payee_name' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['expense_number'] = Expense::generateExpenseNumber();
        $validated['user_id'] = auth()->id();
        $validated['branch_id'] = $validated['branch_id'] ?? auth()->user()->branch_id;
        $validated['status'] = 'pending';

        $expense = Expense::create($validated);

        activity()
            ->performedOn($expense)
            ->causedBy(auth()->user())
            ->log('Created expense: ' . $expense->expense_number);

        return redirect()->route('accounting.expenses.index')
            ->with('success', 'Expense created successfully. Awaiting approval.');
    }

    public function show(Expense $expense)
    {
        $expense->load(['account', 'bank', 'branch', 'user', 'approvedBy']);
        return view('accounting.expenses.show', compact('expense'));
    }

    public function approve(Request $request, Expense $expense)
    {
        try {
            $expense->approve(auth()->id());

            activity()
                ->performedOn($expense)
                ->causedBy(auth()->user())
                ->log('Approved expense: ' . $expense->expense_number);

            return response()->json([
                'success' => true,
                'message' => 'Expense approved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function post(Request $request, Expense $expense)
    {
        try {
            $expense->post();

            activity()
                ->performedOn($expense)
                ->causedBy(auth()->user())
                ->log('Posted expense: ' . $expense->expense_number);

            return response()->json([
                'success' => true,
                'message' => 'Expense posted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function reject(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $expense->reject(auth()->id(), $validated['reason']);

        activity()
            ->performedOn($expense)
            ->causedBy(auth()->user())
            ->log('Rejected expense: ' . $expense->expense_number . ' - Reason: ' . $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Expense rejected.',
        ]);
    }
}

