<?php

namespace App\Services;

use App\Models\Loan;
use Carbon\Carbon;

class LoanCalculationService
{
    /**
     * Calculate simple interest (Principal × Rate)
     * This focuses only on percentage and principal, ignoring duration for interest calculation
     */
    public function calculateSimpleInterest($principal, $interestRate)
    {
        // Simple interest: Principal × (Interest Rate / 100)
        $interest = $principal * ($interestRate / 100);
        
        return [
            'interest_amount' => round($interest, 2),
            'total_amount' => round($principal + $interest, 2),
            'principal' => round($principal, 2),
            'interest_rate' => $interestRate,
        ];
    }
    
    /**
     * Calculate loan amortization schedule
     */
    public function calculateAmortizationSchedule($principal, $annualInterestRate, $termMonths, $startDate = null)
    {
        // Validate inputs
        if ($principal <= 0 || $termMonths <= 0) {
            throw new \InvalidArgumentException('Principal and term must be greater than zero');
        }
        
        $startDate = $startDate ? Carbon::parse($startDate) : now();
        $monthlyRate = $annualInterestRate / 100 / 12;
        
        // Handle zero interest rate case
        if ($annualInterestRate == 0 || $monthlyRate == 0) {
            $monthlyPayment = $principal / $termMonths;
            $totalInterest = 0;
        } else {
            // Calculate monthly payment using amortization formula
            $denominator = pow(1 + $monthlyRate, $termMonths) - 1;
            
            // Prevent division by zero
            if ($denominator == 0) {
                $monthlyPayment = $principal / $termMonths;
                $totalInterest = 0;
            } else {
                $monthlyPayment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / $denominator;
            }
        }
        
        $schedule = [];
        $balance = $principal;
        $totalInterest = 0;
        
        for ($month = 1; $month <= $termMonths; $month++) {
            $interestPayment = $balance * $monthlyRate;
            $principalPayment = $monthlyPayment - $interestPayment;
            $balance -= $principalPayment;
            
            // Adjust last payment for rounding
            if ($month == $termMonths && $balance != 0) {
                $principalPayment += $balance;
                $monthlyPayment = $principalPayment + $interestPayment;
                $balance = 0;
            }
            
            $totalInterest += $interestPayment;
            
            $schedule[] = [
                'payment_number' => $month,
                'due_date' => $startDate->copy()->addMonths($month),
                'payment_amount' => round($monthlyPayment, 2),
                'principal' => round($principalPayment, 2),
                'interest' => round($interestPayment, 2),
                'balance' => round(max($balance, 0), 2),
                'status' => 'pending',
            ];
        }
        
        return [
            'schedule' => $schedule,
            'monthly_payment' => round($monthlyPayment, 2),
            'total_interest' => round($totalInterest, 2),
            'total_amount' => round($principal + $totalInterest, 2),
        ];
    }

    /**
     * Calculate outstanding balance with interest
     */
    public function calculateOutstandingBalance(Loan $loan)
    {
        if (!$loan->repayment_schedule) {
            return $loan->principal_amount;
        }

        $schedule = json_decode($loan->repayment_schedule, true);
        $totalPaid = $loan->total_paid ?? 0;
        
        // Calculate remaining balance from schedule
        $remainingBalance = 0;
        foreach ($schedule as $payment) {
            if ($payment['status'] !== 'paid') {
                $remainingBalance += $payment['payment_amount'];
            }
        }
        
        return max($remainingBalance - $totalPaid, 0);
    }

    /**
     * Get next payment details
     */
    public function getNextPayment(Loan $loan)
    {
        if (!$loan->repayment_schedule) {
            return null;
        }

        $schedule = json_decode($loan->repayment_schedule, true);
        
        foreach ($schedule as $payment) {
            if ($payment['status'] === 'pending') {
                return $payment;
            }
        }
        
        return null;
    }

    /**
     * Update loan with calculated values using SIMPLE INTEREST
     * Simple Interest = Principal × (Rate / 100)
     */
    public function updateLoanCalculations(Loan $loan)
    {
        // Ensure we have valid data
        $principal = $loan->principal_amount ?? $loan->amount;
        $interestRate = $loan->interest_rate ?? 12;
        $termMonths = $loan->loan_term ?? $loan->term_months;
        
        if (!$principal || !$termMonths || $principal <= 0 || $termMonths <= 0) {
            \Log::warning("Cannot calculate loan schedule - invalid data", [
                'loan_id' => $loan->id,
                'principal' => $principal,
                'term' => $termMonths
            ]);
            return null;
        }
        
        try {
            // Use SIMPLE interest calculation: Principal × (Rate / 100)
            $simpleInterestCalc = $this->calculateSimpleInterest($principal, $interestRate);
            
            $totalAmount = $simpleInterestCalc['total_amount'];
            $interestAmount = $simpleInterestCalc['interest_amount'];
            $monthlyPayment = $totalAmount / $termMonths;
            
            // Build simple repayment schedule
            $schedule = [];
            $balance = $totalAmount;
            $startDate = $loan->disbursement_date ?? now();
            
            for ($month = 1; $month <= $termMonths; $month++) {
                $dueDate = Carbon::parse($startDate)->addMonths($month);
                $payment = round($monthlyPayment, 2);
                $balance -= $payment;
                
                $schedule[] = [
                    'payment_number' => $month,
                    'due_date' => $dueDate,
                    'payment_amount' => $payment,
                    'principal' => round($principal / $termMonths, 2),
                    'interest' => round($interestAmount / $termMonths, 2),
                    'balance' => round(max($balance, 0), 2),
                    'status' => 'pending',
                ];
            }
            
            $calculation = [
                'monthly_payment' => round($monthlyPayment, 2),
                'total_interest' => $interestAmount,
                'total_amount' => $totalAmount,
                'schedule' => $schedule,
            ];
            
            // Use withoutEvents() to prevent infinite observer loops
            $loan->withoutEvents(function () use ($loan, $calculation) {
                $updateData = [
                    'monthly_payment' => $calculation['monthly_payment'],
                    'total_interest' => $calculation['total_interest'],
                    'total_amount' => $calculation['total_amount'],
                    'outstanding_balance' => $calculation['total_amount'] - ($loan->total_paid ?? 0),
                    'repayment_schedule' => json_encode($calculation['schedule']),
                ];
                
                // Set next payment details if schedule is available
                if (!empty($calculation['schedule'])) {
                    $nextPayment = $calculation['schedule'][0];
                    $updateData['next_due_date'] = $nextPayment['due_date'];
                    $updateData['next_payment_amount'] = $nextPayment['payment_amount'];
                }
                
                $loan->update($updateData);
            });
            
            return $calculation;
        } catch (\Exception $e) {
            \Log::error('Loan calculation error: ' . $e->getMessage(), [
                'loan_id' => $loan->id,
                'principal' => $principal,
                'rate' => $interestRate,
                'term' => $termMonths
            ]);
            
            // Fallback: simple calculation without interest
            $loan->withoutEvents(function () use ($loan, $principal, $termMonths) {
                $loan->update([
                    'monthly_payment' => round($principal / $termMonths, 2),
                    'total_interest' => 0,
                    'total_amount' => $principal,
                    'outstanding_balance' => $principal - ($loan->total_paid ?? 0),
                ]);
            });
            
            return null;
        }
    }
}

