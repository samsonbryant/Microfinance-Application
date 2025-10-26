<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\Transfer;
use App\Models\RevenueEntry;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use App\Models\Bank;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;

class AccountingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::first();
        $user = User::where('email', 'admin@microfinance.com')->first() ?? User::first();

        if (!$branch || !$user) {
            $this->command->warn('No branch or user found. Please seed branches and users first.');
            return;
        }

        // Seed some sample expenses
        $this->seedExpenses($branch->id, $user->id);
        
        // Seed some sample revenue entries
        $this->seedRevenues($branch->id, $user->id);
        
        // Seed some sample transfers
        $this->seedTransfers($branch->id, $user->id);
        
        // Seed some sample journal entries
        $this->seedJournalEntries($branch->id, $user->id);

        $this->command->info('Sample accounting data seeded successfully!');
    }

    private function seedExpenses($branchId, $userId)
    {
        $expenseAccounts = ChartOfAccount::where('type', 'expense')->get();
        $cashAccount = ChartOfAccount::where('code', '1000')->first();
        $banks = Bank::where('type', 'bank')->get();

        $expenses = [];
        
        for ($i = 1; $i <= 10; $i++) {
            $date = Carbon::now()->subDays(rand(1, 60));
            $account = $expenseAccounts->random();
            $amount = rand(100, 5000);
            $paymentMethod = rand(0, 1) ? 'cash' : 'cheque';
            
            $expense = Expense::create([
                'expense_number' => 'EXP' . $date->format('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                'transaction_date' => $date,
                'account_id' => $account->id,
                'description' => "Sample expense for {$account->name}",
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'bank_id' => $paymentMethod === 'cheque' ? $banks->random()->id : null,
                'reference_number' => $paymentMethod === 'cheque' ? 'CHQ' . rand(1000, 9999) : null,
                'payee_name' => 'Sample Payee ' . $i,
                'branch_id' => $branchId,
                'user_id' => $userId,
                'status' => 'posted',
                'approved_by' => $userId,
                'approved_at' => $date->copy()->addHours(1),
                'posted_at' => $date->copy()->addHours(2),
            ]);

            // Auto-post the expense
            try {
                $expense->post();
            } catch (\Exception $e) {
                // Already posted
            }
        }
    }

    private function seedRevenues($branchId, $userId)
    {
        $revenueAccounts = ChartOfAccount::where('type', 'revenue')->get();
        $revenueTypes = ['interest_received', 'default_charges', 'processing_fee', 'system_charge', 'other'];

        for ($i = 1; $i <= 10; $i++) {
            $date = Carbon::now()->subDays(rand(1, 60));
            $account = $revenueAccounts->random();
            $amount = rand(500, 10000);
            $revenueType = $revenueTypes[array_rand($revenueTypes)];
            
            $revenue = RevenueEntry::create([
                'revenue_number' => 'REV' . $date->format('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                'transaction_date' => $date,
                'account_id' => $account->id,
                'revenue_type' => $revenueType,
                'description' => "Sample revenue - {$revenueType}",
                'amount' => $amount,
                'reference_number' => 'REF' . rand(1000, 9999),
                'branch_id' => $branchId,
                'user_id' => $userId,
                'status' => 'posted',
                'approved_by' => $userId,
                'approved_at' => $date->copy()->addHours(1),
                'posted_at' => $date->copy()->addHours(2),
            ]);

            // Auto-post the revenue
            try {
                $revenue->post();
            } catch (\Exception $e) {
                // Already posted
            }
        }
    }

    private function seedTransfers($branchId, $userId)
    {
        $accounts = ChartOfAccount::where('is_active', true)->get();
        $banks = Bank::all();

        for ($i = 1; $i <= 5; $i++) {
            $date = Carbon::now()->subDays(rand(1, 60));
            $fromAccount = $accounts->random();
            $toAccount = $accounts->where('id', '!=', $fromAccount->id)->random();
            $amount = rand(1000, 50000);
            
            $transfer = Transfer::create([
                'transfer_number' => 'TRF' . $date->format('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                'transaction_date' => $date,
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'from_bank_id' => rand(0, 1) ? $banks->random()->id : null,
                'to_bank_id' => rand(0, 1) ? $banks->random()->id : null,
                'amount' => $amount,
                'type' => ['deposit', 'withdrawal', 'transfer'][array_rand(['deposit', 'withdrawal', 'transfer'])],
                'reference_number' => 'TRF' . rand(1000, 9999),
                'description' => "Sample transfer between accounts",
                'branch_id' => $branchId,
                'user_id' => $userId,
                'status' => 'posted',
                'approved_by' => $userId,
                'approved_at' => $date->copy()->addHours(1),
                'posted_at' => $date->copy()->addHours(2),
            ]);

            // Auto-post the transfer
            try {
                $transfer->post();
            } catch (\Exception $e) {
                // Already posted
            }
        }
    }

    private function seedJournalEntries($branchId, $userId)
    {
        $accounts = ChartOfAccount::where('is_active', true)->get();

        for ($i = 1; $i <= 3; $i++) {
            $date = Carbon::now()->subDays(rand(1, 60));
            $amount = rand(1000, 10000);
            
            $journalEntry = JournalEntry::create([
                'journal_number' => 'JE' . $date->format('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                'transaction_date' => $date,
                'description' => "Sample journal entry {$i}",
                'reference_number' => 'JE' . rand(1000, 9999),
                'branch_id' => $branchId,
                'user_id' => $userId,
                'total_debits' => $amount,
                'total_credits' => $amount,
                'status' => 'posted',
                'approved_by' => $userId,
                'approved_at' => $date->copy()->addHours(1),
                'posted_at' => $date->copy()->addHours(2),
            ]);

            // Create debit line
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $accounts->random()->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Debit entry',
            ]);

            // Create credit line
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $accounts->random()->id,
                'debit' => 0,
                'credit' => $amount,
                'description' => 'Credit entry',
            ]);

            // Auto-post the journal entry
            try {
                $journalEntry->post();
            } catch (\Exception $e) {
                // Already posted
            }
        }
    }
}

