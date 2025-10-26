<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class BankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage_banks')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $banks = Bank::with('account')->latest();

            return DataTables::of($banks)
                ->addColumn('type_badge', function ($bank) {
                    return '<span class="badge bg-' . $bank->getTypeBadgeClass() . '">' . ucfirst($bank->type) . '</span>';
                })
                ->addColumn('balance', function ($bank) {
                    return '$' . $bank->getFormattedBalance();
                })
                ->addColumn('status_badge', function ($bank) {
                    return $bank->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($bank) {
                    $editUrl = route('accounting.banks.edit', $bank);
                    $deleteUrl = route('accounting.banks.destroy', $bank);

                    return '
                        <div class="btn-group" role="group">
                            <a href="' . $editUrl . '" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteBank(' . $bank->id . ')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('accounting.banks.index');
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('type', 'asset')
            ->where('is_active', true)
            ->get();

        return view('accounting.banks.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,mobile_money',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'account_number' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
        ]);

        $bank = Bank::create($validated);

        activity()
            ->performedOn($bank)
            ->causedBy(auth()->user())
            ->log('Created bank: ' . $bank->name);

        return redirect()->route('accounting.banks.index')
            ->with('success', 'Bank created successfully.');
    }

    public function show(Bank $bank)
    {
        $bank->load('account');
        return view('accounting.banks.show', compact('bank'));
    }

    public function edit(Bank $bank)
    {
        $accounts = ChartOfAccount::where('type', 'asset')
            ->where('is_active', true)
            ->get();

        return view('accounting.banks.edit', compact('bank', 'accounts'));
    }

    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,mobile_money',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'account_number' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $bank->update($validated);

        activity()
            ->performedOn($bank)
            ->causedBy(auth()->user())
            ->log('Updated bank: ' . $bank->name);

        return redirect()->route('accounting.banks.index')
            ->with('success', 'Bank updated successfully.');
    }

    public function destroy(Bank $bank)
    {
        activity()
            ->performedOn($bank)
            ->causedBy(auth()->user())
            ->log('Deleted bank: ' . $bank->name);

        $bank->delete();

        return response()->json(['success' => true]);
    }
}

