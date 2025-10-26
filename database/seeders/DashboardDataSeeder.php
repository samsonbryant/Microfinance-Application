<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\User;
use App\Models\Client;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Models\LoanRepayment;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DashboardDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Dashboard Data Seeder...');

        // Create roles if they don't exist
        $roles = ['admin', 'branch_manager', 'loan_officer', 'borrower'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Create branches
        $this->command->info('Creating branches...');
        $mainBranch = Branch::firstOrCreate(
            ['code' => 'BR001'],
            [
                'name' => 'Main Branch',
                'address' => '123 Main Street',
                'city' => 'Main City',
                'phone' => '+1234567890',
                'email' => 'main@microfinance.com',
                'is_active' => true
            ]
        );

        $eastBranch = Branch::firstOrCreate(
            ['code' => 'BR002'],
            [
                'name' => 'East Branch',
                'address' => '456 East Avenue',
                'city' => 'East City',
                'phone' => '+1234567891',
                'email' => 'east@microfinance.com',
                'is_active' => true
            ]
        );

        // Create admin user
        $this->command->info('Creating admin user...');
        $admin = User::firstOrCreate(
            ['email' => 'admin@microfinance.com'],
            [
                'name' => 'System Admin',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'branch_id' => $mainBranch->id
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create branch manager
        $this->command->info('Creating branch manager...');
        $branchManager = User::firstOrCreate(
            ['email' => 'manager@microfinance.com'],
            [
                'name' => 'Branch Manager',
                'username' => 'manager',
                'password' => Hash::make('password'),
                'branch_id' => $mainBranch->id
            ]
        );
        if (!$branchManager->hasRole('branch_manager')) {
            $branchManager->assignRole('branch_manager');
        }

        // Create loan officer
        $this->command->info('Creating loan officer...');
        $loanOfficer = User::firstOrCreate(
            ['email' => 'officer@microfinance.com'],
            [
                'name' => 'Loan Officer',
                'username' => 'officer',
                'password' => Hash::make('password'),
                'branch_id' => $mainBranch->id
            ]
        );
        if (!$loanOfficer->hasRole('loan_officer')) {
            $loanOfficer->assignRole('loan_officer');
        }

        // Create borrower users and clients with loans
        $this->command->info('Creating borrowers and loans...');
        for ($i = 1; $i <= 10; $i++) {
            $borrowerUser = User::firstOrCreate(
                ['email' => "borrower{$i}@example.com"],
                [
                    'name' => "Borrower {$i}",
                    'username' => "borrower{$i}",
                    'password' => Hash::make('password'),
                    'branch_id' => $i <= 5 ? $mainBranch->id : $eastBranch->id
                ]
            );
            if (!$borrowerUser->hasRole('borrower')) {
                $borrowerUser->assignRole('borrower');
            }

            $client = Client::firstOrCreate(
                ['email' => "borrower{$i}@example.com"],
                [
                    'user_id' => $borrowerUser->id,
                    'client_number' => 'CL' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'first_name' => "Borrower",
                    'last_name' => "Number {$i}",
                    'phone' => '+1234' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'date_of_birth' => Carbon::now()->subYears(rand(25, 60)),
                    'gender' => $i % 2 == 0 ? 'male' : 'female',
                    'address' => "{$i} Client Street",
                    'city' => 'City ' . $i,
                    'occupation' => ['farmer', 'trader', 'teacher', 'businessman'][rand(0, 3)],
                    'branch_id' => $i <= 5 ? $mainBranch->id : $eastBranch->id,
                    'created_by' => $loanOfficer->id
                ]
            );

            // Create 1-3 loans per client
            $loanCount = rand(1, 3);
            for ($j = 0; $j < $loanCount; $j++) {
                $amount = rand(1000, 50000);
                $interestRate = rand(10, 20);
                $termMonths = rand(6, 36);
                $disbursementDate = Carbon::now()->subMonths(rand(0, 12));
                $daysOverdue = rand(0, 30);
                
                // Determine loan status
                $statusOptions = ['active', 'disbursed', 'pending'];
                if ($daysOverdue > 0 && rand(0, 1)) {
                    $statusOptions[] = 'overdue';
                }
                $status = $statusOptions[array_rand($statusOptions)];
                
                $outstandingBalance = $amount * (1 + $interestRate / 100) * (rand(30, 90) / 100);

                $loan = Loan::create([
                    'loan_number' => 'LN' . str_pad(($i * 10 + $j), 6, '0', STR_PAD_LEFT),
                    'client_id' => $client->id,
                    'amount' => $amount,
                    'principal_amount' => $amount,
                    'interest_rate' => $interestRate,
                    'term_months' => $termMonths,
                    'payment_frequency' => 'monthly',
                    'disbursement_date' => $status !== 'pending' ? $disbursementDate : null,
                    'first_payment_date' => $status !== 'pending' ? $disbursementDate->copy()->addMonth() : null,
                    'maturity_date' => $status !== 'pending' ? $disbursementDate->copy()->addMonths($termMonths) : null,
                    'status' => $status,
                    'outstanding_balance' => $status === 'pending' ? $amount : $outstandingBalance,
                    'purpose' => ['business', 'agriculture', 'education', 'home improvement'][rand(0, 3)],
                    'branch_id' => $client->branch_id,
                    'created_by' => $loanOfficer->id,
                    'approved_by' => $status !== 'pending' ? $branchManager->id : null,
                    'approved_at' => $status !== 'pending' ? $disbursementDate->copy()->subDays(2) : null,
                    'next_due_date' => $status === 'active' || $status === 'overdue' ? 
                        Carbon::now()->addDays(rand(-$daysOverdue, 30)) : null,
                    'next_payment_amount' => $status === 'active' || $status === 'overdue' ? 
                        round($outstandingBalance / rand(6, 12), 2) : null,
                ]);

                // Create repayments for active/disbursed loans
                if (in_array($status, ['active', 'disbursed', 'overdue'])) {
                    $paymentsCount = rand(1, min(6, $termMonths));
                    for ($k = 0; $k < $paymentsCount; $k++) {
                        $paymentAmount = $amount / $termMonths;
                        
                        LoanRepayment::create([
                            'loan_id' => $loan->id,
                            'amount' => $paymentAmount,
                            'principal_amount' => $paymentAmount * 0.8,
                            'interest_amount' => $paymentAmount * 0.2,
                            'payment_date' => $disbursementDate->copy()->addMonths($k + 1),
                            'payment_method' => ['cash', 'bank_transfer', 'mobile_money'][rand(0, 2)],
                            'status' => 'completed',
                            'received_by' => $loanOfficer->id
                        ]);
                    }
                }
            }

            // Create savings account for client
            SavingsAccount::create([
                'account_number' => 'SA' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'account_type' => ['regular', 'fixed', 'recurring'][rand(0, 2)],
                'balance' => rand(100, 10000),
                'interest_rate' => rand(2, 8),
                'status' => 'active',
                'branch_id' => $client->branch_id,
                'created_by' => $loanOfficer->id,
                'last_deposit_date' => Carbon::now()->subDays(rand(1, 30))
            ]);

            // Create some transactions
            for ($t = 0; $t < rand(2, 5); $t++) {
                Transaction::create([
                    'transaction_number' => 'TXN' . str_pad(($i * 10 + $t), 8, '0', STR_PAD_LEFT),
                    'client_id' => $client->id,
                    'type' => ['deposit', 'withdrawal', 'loan_payment'][rand(0, 2)],
                    'amount' => rand(100, 5000),
                    'description' => 'Sample transaction ' . ($t + 1),
                    'status' => 'completed',
                    'branch_id' => $client->branch_id,
                    'created_by' => $loanOfficer->id,
                    'transaction_date' => Carbon::now()->subDays(rand(1, 60))
                ]);
            }
        }

        $this->command->info('Dashboard data seeded successfully!');
        $this->command->info('-------------------------------------------');
        $this->command->info('Test Credentials:');
        $this->command->info('Admin: admin@microfinance.com / password');
        $this->command->info('Manager: manager@microfinance.com / password');
        $this->command->info('Officer: officer@microfinance.com / password');
        $this->command->info('Borrower: borrower1@example.com / password');
        $this->command->info('-------------------------------------------');
    }
}

