<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientRiskProfile;
use App\Models\Loan;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\DB;

class RiskAssessmentService
{
    /**
     * Calculate client risk score
     */
    public function calculateRiskScore($clientId)
    {
        $client = Client::with(['loans', 'savingsAccounts', 'transactions'])->findOrFail($clientId);
        
        $riskFactors = [
            'credit_history' => $this->assessCreditHistory($client),
            'income_stability' => $this->assessIncomeStability($client),
            'debt_to_income' => $this->assessDebtToIncomeRatio($client),
            'savings_behavior' => $this->assessSavingsBehavior($client),
            'payment_history' => $this->assessPaymentHistory($client),
            'business_stability' => $this->assessBusinessStability($client),
        ];

        $totalScore = array_sum($riskFactors);
        $maxScore = count($riskFactors) * 100;
        $riskPercentage = ($totalScore / $maxScore) * 100;

        // Determine risk level
        $riskLevel = $this->determineRiskLevel($riskPercentage);

        // Save or update risk profile
        ClientRiskProfile::updateOrCreate(
            ['client_id' => $clientId],
            [
                'risk_score' => $riskPercentage,
                'risk_level' => $riskLevel,
                'risk_factors' => $riskFactors,
                'last_assessed' => now(),
                'assessed_by' => auth()->id(),
            ]
        );

        return [
            'risk_score' => $riskPercentage,
            'risk_level' => $riskLevel,
            'risk_factors' => $riskFactors,
        ];
    }

    /**
     * Assess credit history
     */
    private function assessCreditHistory($client)
    {
        $loans = $client->loans;
        
        if ($loans->isEmpty()) {
            return 50; // Neutral score for new clients
        }

        $totalLoans = $loans->count();
        $completedLoans = $loans->where('status', 'completed')->count();
        $overdueLoans = $loans->where('status', 'overdue')->count();
        $defaultedLoans = $loans->where('status', 'defaulted')->count();

        $completionRate = $totalLoans > 0 ? ($completedLoans / $totalLoans) * 100 : 0;
        $overdueRate = $totalLoans > 0 ? ($overdueLoans / $totalLoans) * 100 : 0;
        $defaultRate = $totalLoans > 0 ? ($defaultedLoans / $totalLoans) * 100 : 0;

        $score = 100;
        $score -= $overdueRate * 2; // Penalty for overdue loans
        $score -= $defaultRate * 5; // Heavy penalty for defaults
        $score += $completionRate * 0.5; // Bonus for completed loans

        return max(0, min(100, $score));
    }

    /**
     * Assess income stability
     */
    private function assessIncomeStability($client)
    {
        $monthlyIncome = $client->monthly_income ?? 0;
        
        if ($monthlyIncome == 0) {
            return 20; // Low score for no income
        }

        // Score based on income level
        if ($monthlyIncome >= 50000) {
            return 90;
        } elseif ($monthlyIncome >= 30000) {
            return 80;
        } elseif ($monthlyIncome >= 20000) {
            return 70;
        } elseif ($monthlyIncome >= 10000) {
            return 60;
        } else {
            return 40;
        }
    }

    /**
     * Assess debt-to-income ratio
     */
    private function assessDebtToIncomeRatio($client)
    {
        $monthlyIncome = $client->monthly_income ?? 1; // Avoid division by zero
        $activeLoans = $client->loans->whereIn('status', ['active', 'disbursed']);
        
        $totalMonthlyPayments = $activeLoans->sum(function($loan) {
            return $loan->calculateMonthlyPayment();
        });

        $debtToIncomeRatio = ($totalMonthlyPayments / $monthlyIncome) * 100;

        if ($debtToIncomeRatio <= 30) {
            return 90; // Excellent
        } elseif ($debtToIncomeRatio <= 40) {
            return 80; // Good
        } elseif ($debtToIncomeRatio <= 50) {
            return 60; // Fair
        } elseif ($debtToIncomeRatio <= 60) {
            return 40; // Poor
        } else {
            return 20; // Very poor
        }
    }

    /**
     * Assess savings behavior
     */
    private function assessSavingsBehavior($client)
    {
        $savingsAccounts = $client->savingsAccounts;
        
        if ($savingsAccounts->isEmpty()) {
            return 30; // Low score for no savings
        }

        $totalSavings = $savingsAccounts->sum('balance');
        $monthlyIncome = $client->monthly_income ?? 1;
        $savingsRatio = ($totalSavings / $monthlyIncome) * 100;

        if ($savingsRatio >= 100) {
            return 95; // Excellent savings
        } elseif ($savingsRatio >= 50) {
            return 80; // Good savings
        } elseif ($savingsRatio >= 25) {
            return 60; // Fair savings
        } elseif ($savingsRatio >= 10) {
            return 40; // Poor savings
        } else {
            return 20; // Very poor savings
        }
    }

    /**
     * Assess payment history
     */
    private function assessPaymentHistory($client)
    {
        $transactions = $client->transactions->where('type', 'repayment');
        
        if ($transactions->isEmpty()) {
            return 50; // Neutral for no payment history
        }

        $totalTransactions = $transactions->count();
        $onTimePayments = $transactions->where('status', 'completed')->count();
        $latePayments = $transactions->where('status', 'late')->count();

        $onTimeRate = $totalTransactions > 0 ? ($onTimePayments / $totalTransactions) * 100 : 0;
        $lateRate = $totalTransactions > 0 ? ($latePayments / $totalTransactions) * 100 : 0;

        $score = $onTimeRate;
        $score -= $lateRate * 2; // Penalty for late payments

        return max(0, min(100, $score));
    }

    /**
     * Assess business stability
     */
    private function assessBusinessStability($client)
    {
        // This would typically involve more complex business analysis
        // For now, we'll use a simplified approach based on client data
        
        $clientAge = $client->created_at->diffInYears(now());
        $hasActiveLoans = $client->loans->whereIn('status', ['active', 'disbursed'])->isNotEmpty();
        $hasSavings = $client->savingsAccounts->isNotEmpty();

        $score = 50; // Base score
        
        if ($clientAge >= 2) {
            $score += 20; // Bonus for long-term client
        }
        
        if ($hasActiveLoans) {
            $score += 10; // Bonus for active relationship
        }
        
        if ($hasSavings) {
            $score += 10; // Bonus for savings relationship
        }

        return min(100, $score);
    }

    /**
     * Determine risk level based on score
     */
    private function determineRiskLevel($score)
    {
        if ($score >= 80) {
            return 'low';
        } elseif ($score >= 60) {
            return 'medium';
        } elseif ($score >= 40) {
            return 'high';
        } else {
            return 'very_high';
        }
    }

    /**
     * Get risk assessment for loan application
     */
    public function assessLoanApplication($applicationId)
    {
        $application = LoanApplication::with(['client'])->findOrFail($applicationId);
        $client = $application->client;
        
        // Get existing risk profile or create new one
        $riskProfile = $this->calculateRiskScore($client->id);
        
        // Additional factors for loan application
        $loanAmount = $application->requested_amount;
        $monthlyIncome = $client->monthly_income ?? 1;
        $loanToIncomeRatio = ($loanAmount / $monthlyIncome) * 100;
        
        // Adjust risk based on loan amount
        if ($loanToIncomeRatio > 200) {
            $riskProfile['risk_score'] += 20; // Increase risk for high loan-to-income ratio
        } elseif ($loanToIncomeRatio > 150) {
            $riskProfile['risk_score'] += 10;
        }
        
        // Update risk level
        $riskProfile['risk_level'] = $this->determineRiskLevel($riskProfile['risk_score']);
        
        return $riskProfile;
    }

    /**
     * Get portfolio risk metrics
     */
    public function getPortfolioRiskMetrics($branchId = null)
    {
        $query = ClientRiskProfile::with('client');
        
        if ($branchId) {
            $query->whereHas('client', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $riskProfiles = $query->get();

        return [
            'total_clients' => $riskProfiles->count(),
            'low_risk' => $riskProfiles->where('risk_level', 'low')->count(),
            'medium_risk' => $riskProfiles->where('risk_level', 'medium')->count(),
            'high_risk' => $riskProfiles->where('risk_level', 'high')->count(),
            'very_high_risk' => $riskProfiles->where('risk_level', 'very_high')->count(),
            'average_risk_score' => $riskProfiles->avg('risk_score'),
            'risk_distribution' => $riskProfiles->groupBy('risk_level')->map->count(),
        ];
    }
}
