<?php

namespace App\Http\Controllers;

use App\Models\RevenueEntry;
use App\Models\ChartOfAccount;
use App\Models\Bank;
use App\Models\Branch;
use App\Models\Loan;
use App\Models\Client;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class RevenueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage_revenues')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $revenues = RevenueEntry::with(['account', 'bank', 'loan', 'client', 'branch', 'user'])
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->revenue_type, function ($query, $type) {
                    return $query->where('revenue_type', $type);
                })
                ->when($request->from_date, function ($query, $date) {
                    return $query->whereDate('transaction_date', '>=', $date);
                })
                ->when($request->to_date, function ($query, $date) {
                    return $query->whereDate('transaction_date', '<=', $date);
                })
                ->latest();

            return DataTables::of($revenues)
                ->addColumn('date', function ($revenue) {
                    return $revenue->transaction_date->format('Y-m-d');
                })
                ->addColumn('account_name', function ($revenue) {
                    return $revenue->account->name ?? 'N/A';
                })
                ->addColumn('amount', function ($revenue) {
                    return '$' . number_format($revenue->amount, 2);
                })
                ->addColumn('type_badge', function ($revenue) {
                    $colors = [
                        'interest_received' => 'success',
                        'default_charges' => 'warning',
                        'processing_fee' => 'primary',
                        'system_charge' => 'info',
                        'other' => 'secondary',
                    ];
                    return '<span class="badge bg-' . ($colors[$revenue->revenue_type] ?? 'secondary') . '">' . ucfirst(str_replace('_', ' ', $revenue->revenue_type)) . '</span>';
                })
                ->addColumn('status_badge', function ($revenue) {
                    return '<span class="badge bg-' . $revenue->getStatusBadgeClass() . '">' . ucfirst($revenue->status) . '</span>';
                })
                ->addColumn('action', function ($revenue) {
                    $showUrl = route('accounting.revenues.show', $revenue);

                    $buttons = '<div class="btn-group" role="group">';
                    $buttons .= '<a href="' . $showUrl . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';

                    if ($revenue->canBeApproved() && auth()->user()->can('approve_revenues')) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-success" onclick="approveRevenue(' . $revenue->id . ')"><i class="fas fa-check"></i></button>';
                    }

                    if ($revenue->canBePosted() && auth()->user()->can('post_revenues')) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-primary" onclick="postRevenue(' . $revenue->id . ')"><i class="fas fa-paper-plane"></i></button>';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('accounting.revenues.index');
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('type', 'revenue')
            ->where('is_active', true)
            ->get();
        $banks = Bank::where('is_active', true)->get();
        $branches = Branch::all();

        return view('accounting.revenues.create', compact('accounts', 'banks', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'revenue_type' => 'required|in:interest_received,default_charges,processing_fee,system_charge,other',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'bank_id' => 'nullable|exists:banks,id',
            'reference_number' => 'nullable|string|max:255',
            'loan_id' => 'nullable|exists:loans,id',
            'client_id' => 'nullable|exists:clients,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['revenue_number'] = RevenueEntry::generateRevenueNumber();
        $validated['user_id'] = auth()->id();
        $validated['branch_id'] = $validated['branch_id'] ?? auth()->user()->branch_id;
        $validated['status'] = 'pending';

        $revenue = RevenueEntry::create($validated);

        activity()
            ->performedOn($revenue)
            ->causedBy(auth()->user())
            ->log('Created revenue entry: ' . $revenue->revenue_number);

        return redirect()->route('accounting.revenues.index')
            ->with('success', 'Revenue entry created successfully. Awaiting approval.');
    }

    public function show(RevenueEntry $revenue)
    {
        $revenue->load(['account', 'bank', 'loan', 'client', 'branch', 'user', 'approvedBy']);
        return view('accounting.revenues.show', compact('revenue'));
    }

    public function approve(Request $request, RevenueEntry $revenue)
    {
        try {
            $revenue->approve(auth()->id());

            activity()
                ->performedOn($revenue)
                ->causedBy(auth()->user())
                ->log('Approved revenue entry: ' . $revenue->revenue_number);

            return response()->json([
                'success' => true,
                'message' => 'Revenue entry approved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function post(Request $request, RevenueEntry $revenue)
    {
        try {
            $revenue->post();

            activity()
                ->performedOn($revenue)
                ->causedBy(auth()->user())
                ->log('Posted revenue entry: ' . $revenue->revenue_number);

            return response()->json([
                'success' => true,
                'message' => 'Revenue entry posted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function reject(Request $request, RevenueEntry $revenue)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $revenue->reject(auth()->id(), $validated['reason']);

        activity()
            ->performedOn($revenue)
            ->causedBy(auth()->user())
            ->log('Rejected revenue entry: ' . $revenue->revenue_number . ' - Reason: ' . $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Revenue entry rejected.',
        ]);
    }
}

