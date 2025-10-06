<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|general_manager');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = ChartOfAccount::with('parent', 'children')
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        return view('chart-of-accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentAccounts = ChartOfAccount::whereNull('parent_id')
            ->orderBy('code')
            ->get();

        return view('chart-of-accounts.create', compact('parentAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:chart_of_accounts',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'normal_balance' => 'required|in:debit,credit',
        ]);

        ChartOfAccount::create($request->all());

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Chart of account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load('parent', 'children', 'generalLedgers');
        
        return view('chart-of-accounts.show', compact('chartOfAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartOfAccount $chartOfAccount)
    {
        $parentAccounts = ChartOfAccount::whereNull('parent_id')
            ->where('id', '!=', $chartOfAccount->id)
            ->orderBy('code')
            ->get();

        return view('chart-of-accounts.edit', compact('chartOfAccount', 'parentAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:chart_of_accounts,code,' . $chartOfAccount->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'normal_balance' => 'required|in:debit,credit',
        ]);

        $chartOfAccount->update($request->all());

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Chart of account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        // Check if account has transactions
        if ($chartOfAccount->generalLedgers()->count() > 0) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account with existing transactions.');
        }

        // Check if account has children
        if ($chartOfAccount->children()->count() > 0) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'Cannot delete account with sub-accounts.');
        }

        $chartOfAccount->delete();

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Chart of account deleted successfully.');
    }

    /**
     * Toggle account status
     */
    public function toggleStatus(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->update(['is_active' => !$chartOfAccount->is_active]);

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Account status updated successfully.');
    }

    /**
     * Get account balance
     */
    public function getBalance(ChartOfAccount $chartOfAccount)
    {
        $balance = $chartOfAccount->getBalance();
        
        return response()->json([
            'balance' => $balance,
            'formatted_balance' => number_format($balance, 2),
        ]);
    }
}
