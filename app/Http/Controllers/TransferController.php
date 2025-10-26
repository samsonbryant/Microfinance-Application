<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\ChartOfAccount;
use App\Models\Bank;
use App\Models\Branch;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage_transfers')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $transfers = Transfer::with(['fromAccount', 'toAccount', 'fromBank', 'toBank', 'branch', 'user'])
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->type, function ($query, $type) {
                    return $query->where('type', $type);
                })
                ->when($request->from_date, function ($query, $date) {
                    return $query->whereDate('transaction_date', '>=', $date);
                })
                ->when($request->to_date, function ($query, $date) {
                    return $query->whereDate('transaction_date', '<=', $date);
                })
                ->latest();

            return DataTables::of($transfers)
                ->addColumn('date', function ($transfer) {
                    return $transfer->transaction_date->format('Y-m-d');
                })
                ->addColumn('from', function ($transfer) {
                    return $transfer->fromAccount->name ?? 'N/A';
                })
                ->addColumn('to', function ($transfer) {
                    return $transfer->toAccount->name ?? 'N/A';
                })
                ->addColumn('amount', function ($transfer) {
                    return '$' . number_format($transfer->amount, 2);
                })
                ->addColumn('type_badge', function ($transfer) {
                    $colors = [
                        'deposit' => 'success',
                        'withdrawal' => 'warning',
                        'disbursement' => 'primary',
                        'expense' => 'danger',
                        'transfer' => 'info',
                    ];
                    return '<span class="badge bg-' . ($colors[$transfer->type] ?? 'secondary') . '">' . ucfirst($transfer->type) . '</span>';
                })
                ->addColumn('status_badge', function ($transfer) {
                    return '<span class="badge bg-' . $transfer->getStatusBadgeClass() . '">' . ucfirst($transfer->status) . '</span>';
                })
                ->addColumn('action', function ($transfer) {
                    $showUrl = route('accounting.transfers.show', $transfer);

                    $buttons = '<div class="btn-group" role="group">';
                    $buttons .= '<a href="' . $showUrl . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';

                    if ($transfer->canBeApproved() && auth()->user()->can('approve_transfers')) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-success" onclick="approveTransfer(' . $transfer->id . ')"><i class="fas fa-check"></i></button>';
                    }

                    if ($transfer->canBePosted() && auth()->user()->can('post_transfers')) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-primary" onclick="postTransfer(' . $transfer->id . ')"><i class="fas fa-paper-plane"></i></button>';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('accounting.transfers.index');
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('is_active', true)->get();
        $banks = Bank::where('is_active', true)->get();
        $branches = Branch::all();

        return view('accounting.transfers.create', compact('accounts', 'banks', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'from_account_id' => 'required|exists:chart_of_accounts,id',
            'to_account_id' => 'required|exists:chart_of_accounts,id|different:from_account_id',
            'from_bank_id' => 'nullable|exists:banks,id',
            'to_bank_id' => 'nullable|exists:banks,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:deposit,withdrawal,disbursement,expense,transfer',
            'reference_number' => 'nullable|string|max:255',
            'description' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['transfer_number'] = Transfer::generateTransferNumber();
        $validated['user_id'] = auth()->id();
        $validated['branch_id'] = $validated['branch_id'] ?? auth()->user()->branch_id;
        $validated['status'] = 'pending';

        $transfer = Transfer::create($validated);

        activity()
            ->performedOn($transfer)
            ->causedBy(auth()->user())
            ->log('Created transfer: ' . $transfer->transfer_number);

        return redirect()->route('accounting.transfers.index')
            ->with('success', 'Transfer created successfully. Awaiting approval.');
    }

    public function show(Transfer $transfer)
    {
        $transfer->load(['fromAccount', 'toAccount', 'fromBank', 'toBank', 'branch', 'user', 'approvedBy']);
        return view('accounting.transfers.show', compact('transfer'));
    }

    public function approve(Request $request, Transfer $transfer)
    {
        try {
            $transfer->approve(auth()->id());

            activity()
                ->performedOn($transfer)
                ->causedBy(auth()->user())
                ->log('Approved transfer: ' . $transfer->transfer_number);

            return response()->json([
                'success' => true,
                'message' => 'Transfer approved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function post(Request $request, Transfer $transfer)
    {
        try {
            $transfer->post();

            activity()
                ->performedOn($transfer)
                ->causedBy(auth()->user())
                ->log('Posted transfer: ' . $transfer->transfer_number);

            return response()->json([
                'success' => true,
                'message' => 'Transfer posted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function reject(Request $request, Transfer $transfer)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $transfer->reject(auth()->id(), $validated['reason']);

        activity()
            ->performedOn($transfer)
            ->causedBy(auth()->user())
            ->log('Rejected transfer: ' . $transfer->transfer_number . ' - Reason: ' . $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Transfer rejected.',
        ]);
    }
}

