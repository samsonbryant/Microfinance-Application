<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\SavingsAccount;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get dashboard metrics for a specific role.
     */
    public function getMetrics(string $role = 'admin', int $branchId = null): array
    {
        $cacheKey = "dashboard_metrics_{$role}_{$branchId}";
        
        return Cache::remember($cacheKey, 300, function () use ($role, $branchId) {
            $query = $this->getBaseQuery($branchId);
            
            switch ($role) {
                case 'admin':
                    return $this->getAdminMetrics($query);
                case 'branch_manager':
                    return $this->getBranchManagerMetrics($query, $branchId);
                case 'loan_officer':
                    return $this->getLoanOfficerMetrics($query, $branchId);
                case 'borrower':
                    return $this->getBorrowerMetrics($branchId);
                default:
                    return $this->getBasicMetrics($query);
            }
        });
    }

    /**
     * Get base query with branch filtering.
     */
    private function getBaseQuery(int $branchId = null)
    {
        $query = Loan::query();
        
        if ($branchId) {
            $query->whereHas('client', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        
        return $query;
    }

    /**
     * Get admin-level metrics.
     */
    private function getAdminMetrics($query): array
    {
        return [
            'total_clients' => Client::count(),
            'active_loans' => $query->where('status', 'disbursed')->count(),
            'overdue_loans' => $query->where('status', 'overdue')->count(),
            'pending_loans' => $query->where('status', 'pending')->count(),
            'total_outstanding' => $query->where('status', 'disbursed')->sum('outstanding_balance'),
            'overdue_amount' => $query->where('status', 'overdue')->sum('outstanding_balance'),
            'total_savings' => SavingsAccount::sum('balance'),
            'monthly_disbursements' => $this->getMonthlyDisbursements(),
            'monthly_collections' => $this->getMonthlyCollections(),
            'default_rate' => $this->calculateDefaultRate(),
            'portfolio_at_risk' => $this->calculatePortfolioAtRisk(),
            'branch_performance' => $this->getBranchPerformance(),
        ];
    }

    /**
     * Get branch manager metrics.
     */
    private function getBranchManagerMetrics($query, int $branchId): array
    {
        $metrics = $this->getAdminMetrics($query);
        
        // Add branch-specific metrics
        $metrics['branch_clients'] = Client::where('branch_id', $branchId)->count();
        $metrics['branch_loans'] = $query->whereHas('client', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->count();
        
        return $metrics;
    }

    /**
     * Get loan officer metrics.
     */
    private function getLoanOfficerMetrics($query, int $branchId): array
    {
        $userId = auth()->id();
        
        return [
            'my_loans' => $query->where('loan_officer_id', $userId)->count(),
            'my_active_loans' => $query->where('loan_officer_id', $userId)->where('status', 'disbursed')->count(),
            'my_overdue_loans' => $query->where('loan_officer_id', $userId)->where('status', 'overdue')->count(),
            'my_pending_loans' => $query->where('loan_officer_id', $userId)->where('status', 'pending')->count(),
            'my_collections' => $query->where('loan_officer_id', $userId)->where('status', 'overdue')->sum('outstanding_balance'),
        ];
    }

    /**
     * Get borrower metrics.
     */
    private function getBorrowerMetrics(int $userId): array
    {
        $client = Client::where('user_id', $userId)->first();
        
        if (!$client) {
            return [];
        }
        
        $loans = Loan::where('client_id', $client->id);
        
        return [
            'my_loans' => $loans->count(),
            'active_loans' => $loans->where('status', 'disbursed')->count(),
            'overdue_loans' => $loans->where('status', 'overdue')->count(),
            'total_borrowed' => $loans->sum('amount'),
            'outstanding_balance' => $loans->where('status', 'disbursed')->sum('outstanding_balance'),
            'next_payment' => $this->getNextPayment($client->id),
            'savings_balance' => $client->savingsAccounts->sum('balance'),
        ];
    }

    /**
     * Get basic metrics.
     */
    private function getBasicMetrics($query): array
    {
        return [
            'total_clients' => Client::count(),
            'active_loans' => $query->where('status', 'disbursed')->count(),
            'overdue_loans' => $query->where('status', 'overdue')->count(),
            'total_outstanding' => $query->where('status', 'disbursed')->sum('outstanding_balance'),
        ];
    }

    /**
     * Get monthly disbursements for the last 12 months.
     */
    private function getMonthlyDisbursements(): array
    {
        return Loan::selectRaw('strftime("%Y-%m", disbursement_date) as month, SUM(amount) as total')
            ->where('disbursement_date', '>=', now()->subMonths(12))
            ->whereNotNull('disbursement_date')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }

    /**
     * Get monthly collections for the last 12 months.
     */
    private function getMonthlyCollections(): array
    {
        return Transaction::selectRaw('strftime("%Y-%m", created_at) as month, SUM(amount) as total')
            ->where('type', 'repayment')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }

    /**
     * Calculate default rate.
     */
    private function calculateDefaultRate(): float
    {
        $totalLoans = Loan::whereIn('status', ['disbursed', 'overdue', 'completed', 'defaulted'])->count();
        $defaultedLoans = Loan::where('status', 'defaulted')->count();
        
        if ($totalLoans === 0) {
            return 0;
        }
        
        return round(($defaultedLoans / $totalLoans) * 100, 2);
    }

    /**
     * Calculate portfolio at risk.
     */
    private function calculatePortfolioAtRisk(): array
    {
        $totalOutstanding = Loan::where('status', 'disbursed')->sum('outstanding_balance');
        $overdueAmount = Loan::where('status', 'overdue')->sum('outstanding_balance');
        
        $parAmount = $overdueAmount;
        $parPercentage = $totalOutstanding > 0 ? round(($parAmount / $totalOutstanding) * 100, 2) : 0;
        
        return [
            'amount' => $parAmount,
            'percentage' => $parPercentage,
            'overdue_count' => Loan::where('status', 'overdue')->count(),
        ];
    }

    /**
     * Get branch performance data.
     */
    private function getBranchPerformance(): array
    {
        return Branch::withCount(['clients', 'loans'])
            ->withSum('loans', 'amount')
            ->get()
            ->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'clients_count' => $branch->clients_count,
                    'loans_count' => $branch->loans_count,
                    'portfolio_value' => $branch->loans_sum_amount ?? 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get next payment for a client.
     */
    private function getNextPayment(int $clientId): ?array
    {
        $loan = Loan::where('client_id', $clientId)
            ->where('status', 'disbursed')
            ->where('next_due_date', '>=', now())
            ->orderBy('next_due_date')
            ->first();
        
        if (!$loan) {
            return null;
        }
        
        return [
            'amount' => $loan->next_payment_amount,
            'due_date' => $loan->next_due_date->format('Y-m-d'),
            'loan_id' => $loan->id,
        ];
    }

    /**
     * Get recent activities.
     */
    public function getRecentActivities(int $limit = 10): array
    {
        return [
            'recent_transactions' => Transaction::with('client')
                ->latest()
                ->limit($limit)
                ->get(),
            'recent_loans' => Loan::with('client')
                ->latest()
                ->limit($limit)
                ->get(),
            'recent_clients' => Client::latest()
                ->limit($limit)
                ->get(),
        ];
    }

    /**
     * Get monthly trends data.
     */
    public function getMonthlyTrends(): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y-m'));
        }
        
        $disbursements = $this->getMonthlyDisbursements();
        $collections = $this->getMonthlyCollections();
        $newClients = Client::selectRaw('strftime("%Y-%m", created_at) as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();
        
        return $months->map(function ($month) use ($disbursements, $collections, $newClients) {
            return [
                'month' => $month,
                'loans_disbursed' => $disbursements[$month] ?? 0,
                'loans_collected' => $collections[$month] ?? 0,
                'new_clients' => $newClients[$month] ?? 0,
            ];
        })->toArray();
    }

    /**
     * Clear dashboard cache.
     */
    public function clearCache(): void
    {
        Cache::forget('dashboard_metrics_admin_null');
        Cache::forget('dashboard_metrics_branch_manager_null');
        Cache::forget('dashboard_metrics_loan_officer_null');
        Cache::forget('dashboard_metrics_borrower_null');
    }
}