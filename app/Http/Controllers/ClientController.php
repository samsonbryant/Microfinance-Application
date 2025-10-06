<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $query = Client::with(['branch', 'createdBy'])
            ->when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });

        // Filter by status if provided
        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        // Filter by KYC status if provided
        if (request()->has('kyc_status')) {
            $query->where('kyc_status', request('kyc_status'));
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $branches = $user->role === 'admin' ? Branch::all() : collect([$user->branch]);

        return view('clients.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'occupation' => 'required|string|max:255',
            'monthly_income' => 'required|numeric|min:0',
            'branch_id' => 'required|exists:branches,id',
        ]);

        DB::beginTransaction();
        try {
            $client = Client::create([
                'client_number' => $this->generateClientNumber(),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'occupation' => $request->occupation,
                'monthly_income' => $request->monthly_income,
                'status' => 'active',
                'kyc_status' => 'pending',
                'branch_id' => $request->branch_id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('clients.show', $client)
                ->with('success', 'Client created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Error creating client: ' . $e->getMessage());
        }
    }

    public function show(Client $client)
    {
        $client->load(['branch', 'createdBy', 'loans', 'savingsAccounts', 'transactions' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        $stats = [
            'total_loans' => $client->loans()->count(),
            'active_loans' => $client->loans()->where('status', 'active')->count(),
            'total_borrowed' => $client->loans()->sum('amount'),
            'outstanding_balance' => $client->loans()->whereIn('status', ['active', 'overdue'])->sum('outstanding_balance'),
            'total_savings' => $client->savingsAccounts()->where('status', 'active')->sum('balance'),
        ];

        return view('clients.show', compact('client', 'stats'));
    }

    public function edit(Client $client)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $branches = $user->role === 'admin' ? Branch::all() : collect([$user->branch]);

        return view('clients.edit', compact('client', 'branches'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email,' . $client->id,
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'occupation' => 'required|string|max:255',
            'monthly_income' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,suspended',
            'kyc_status' => 'required|in:pending,verified,rejected',
        ]);

        $client->update($request->all());

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        if ($client->loans()->count() > 0 || $client->savingsAccounts()->count() > 0) {
            return back()->with('error', 'Cannot delete client with existing loans or savings accounts.');
        }

        $client->delete();
        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    public function verifyKyc(Client $client)
    {
        $client->update(['kyc_status' => 'verified']);
        return back()->with('success', 'KYC verification completed.');
    }

    public function suspend(Client $client)
    {
        $client->update(['status' => 'suspended']);
        return back()->with('success', 'Client suspended successfully.');
    }

    public function activate(Client $client)
    {
        $client->update(['status' => 'active']);
        return back()->with('success', 'Client activated successfully.');
    }

    private function generateClientNumber()
    {
        $prefix = 'CLT';
        $timestamp = now()->format('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }
}
