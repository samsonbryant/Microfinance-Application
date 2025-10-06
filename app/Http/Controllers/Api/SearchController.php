<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\LoanApplication;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $user = auth()->user();
        $branchId = $user->branch_id;

        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search clients
        $clients = Client::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where(function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('client_number', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function($client) {
                return [
                    'type' => 'client',
                    'id' => $client->id,
                    'title' => $client->full_name,
                    'subtitle' => $client->client_number,
                    'url' => route('clients.show', $client),
                    'icon' => 'fas fa-user'
                ];
            });

        $results = array_merge($results, $clients->toArray());

        // Search loans
        $loans = Loan::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where(function($q) use ($query) {
                $q->where('loan_number', 'like', "%{$query}%")
                  ->orWhereHas('client', function($q) use ($query) {
                      $q->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                  });
            })
            ->with('client')
            ->limit(5)
            ->get()
            ->map(function($loan) {
                return [
                    'type' => 'loan',
                    'id' => $loan->id,
                    'title' => $loan->loan_number,
                    'subtitle' => $loan->client->full_name ?? 'Unknown Client',
                    'url' => route('loans.show', $loan),
                    'icon' => 'fas fa-hand-holding-usd'
                ];
            });

        $results = array_merge($results, $loans->toArray());

        // Search transactions
        $transactions = Transaction::when($branchId && $user->role !== 'admin', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->where(function($q) use ($query) {
                $q->where('transaction_number', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhereHas('client', function($q) use ($query) {
                      $q->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                  });
            })
            ->with('client')
            ->limit(5)
            ->get()
            ->map(function($transaction) {
                return [
                    'type' => 'transaction',
                    'id' => $transaction->id,
                    'title' => $transaction->transaction_number,
                    'subtitle' => $transaction->description,
                    'url' => route('transactions.show', $transaction),
                    'icon' => 'fas fa-exchange-alt'
                ];
            });

        $results = array_merge($results, $transactions->toArray());

        return response()->json([
            'results' => array_slice($results, 0, 10), // Limit total results
            'query' => $query
        ]);
    }
}
