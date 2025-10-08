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
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'identification_type' => 'required|string',
            'identification_number' => 'required|string|max:100',
            'occupation' => 'required|string|max:255',
            'employer' => 'nullable|string|max:255',
            'employee_number' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'monthly_income' => 'required|numeric|min:0',
            'primary_phone_country' => 'nullable|string|max:10',
            'secondary_phone' => 'nullable|string|max:20',
            'secondary_phone_country' => 'nullable|string|max:10',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
            'borrower_files.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,pdf|max:10240',
            'kin_first_name.*' => 'nullable|string|max:255',
            'kin_last_name.*' => 'nullable|string|max:255',
            'kin_relationship.*' => 'nullable|string|max:100',
            'kin_phone.*' => 'nullable|string|max:20',
            'kin_email.*' => 'nullable|email|max:255',
            'kin_address.*' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            // Handle borrower files
            $files = [];
            if ($request->hasFile('borrower_files')) {
                foreach ($request->file('borrower_files') as $file) {
                    $files[] = $file->store('borrower-files', 'public');
                }
            }

            $client = Client::create([
                'client_number' => $this->generateClientNumber(),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'primary_phone_country' => $validated['primary_phone_country'] ?? 'US',
                'secondary_phone' => $validated['secondary_phone'] ?? null,
                'secondary_phone_country' => $validated['secondary_phone_country'] ?? null,
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'marital_status' => $validated['marital_status'] ?? 'single',
                'identification_type' => $validated['identification_type'],
                'identification_number' => $validated['identification_number'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip_code' => $validated['zip_code'],
                'country' => 'United States',
                'occupation' => $validated['occupation'],
                'employer' => $validated['employer'] ?? null,
                'employee_number' => $validated['employee_number'] ?? null,
                'tax_number' => $validated['tax_number'] ?? null,
                'monthly_income' => $validated['monthly_income'],
                'avatar' => $avatarPath,
                'files' => !empty($files) ? json_encode($files) : null,
                'status' => 'active',
                'kyc_status' => 'pending',
                'branch_id' => $validated['branch_id'],
                'created_by' => auth()->id(),
            ]);

            // Create Next of Kin records
            if ($request->has('kin_first_name')) {
                foreach ($request->kin_first_name as $index => $firstName) {
                    if ($firstName) {
                        \App\Models\NextOfKin::create([
                            'client_id' => $client->id,
                            'first_name' => $firstName,
                            'last_name' => $request->kin_last_name[$index] ?? '',
                            'relationship' => $request->kin_relationship[$index] ?? '',
                            'phone' => $request->kin_phone[$index] ?? '',
                            'email' => $request->kin_email[$index] ?? null,
                            'address' => $request->kin_address[$index] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('clients.show', $client)
                ->with('success', 'Client created successfully with all details.');
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
