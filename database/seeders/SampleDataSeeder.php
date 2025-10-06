<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Models\Branch;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the main branch
        $branch = Branch::first();
        
        if (!$branch) {
            $branch = Branch::create([
                'name' => 'Main Branch',
                'code' => 'MB001',
                'address' => '123 Main Street',
                'city' => 'City',
                'state' => 'State',
                'country' => 'Country',
                'phone' => '+1234567890',
                'email' => 'main@microfinance.com',
                'manager_name' => 'Branch Manager',
                'is_active' => true,
            ]);
        }

        // Create sample clients
        $clients = [
            [
                'client_number' => 'CLI-000001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'date_of_birth' => '1985-05-15',
                'gender' => 'male',
                'address' => '123 Oak Street',
                'city' => 'Springfield',
                'state' => 'IL',
                'country' => 'USA',
                'occupation' => 'Teacher',
                'monthly_income' => 3500.00,
                'income_currency' => 'USD',
                'kyc_status' => 'verified',
                'status' => 'active',
                'branch_id' => $branch->id,
                'created_by' => 1,
            ],
            [
                'client_number' => 'CLI-000002',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1234567891',
                'date_of_birth' => '1990-08-22',
                'gender' => 'female',
                'address' => '456 Pine Avenue',
                'city' => 'Springfield',
                'state' => 'IL',
                'country' => 'USA',
                'occupation' => 'Nurse',
                'monthly_income' => 4200.00,
                'income_currency' => 'USD',
                'kyc_status' => 'verified',
                'status' => 'active',
                'branch_id' => $branch->id,
                'created_by' => 1,
            ],
            [
                'client_number' => 'CLI-000003',
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.johnson@example.com',
                'phone' => '+1234567892',
                'date_of_birth' => '1978-12-10',
                'gender' => 'male',
                'address' => '789 Elm Drive',
                'city' => 'Springfield',
                'state' => 'IL',
                'country' => 'USA',
                'occupation' => 'Engineer',
                'monthly_income' => 5500.00,
                'income_currency' => 'USD',
                'kyc_status' => 'verified',
                'status' => 'active',
                'branch_id' => $branch->id,
                'created_by' => 1,
            ],
        ];

        foreach ($clients as $clientData) {
            $client = Client::create($clientData);
            
            // Create savings account for each client
            SavingsAccount::create([
                'account_number' => 'SAV-' . str_pad($client->id, 6, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'branch_id' => $branch->id,
                'account_type' => 'regular',
                'balance' => rand(500, 5000),
                'interest_rate' => 2.5,
                'minimum_balance' => 100.00,
                'status' => 'active',
                'opening_date' => now()->subDays(rand(30, 365)),
                'created_by' => 1,
            ]);

            // Create loan for some clients
            if (rand(0, 1)) {
                $loanAmount = rand(5000, 25000);
                $loan = Loan::create([
                    'loan_number' => 'LOAN-' . str_pad($client->id, 6, '0', STR_PAD_LEFT),
                    'client_id' => $client->id,
                    'branch_id' => $branch->id,
                    'loan_type' => ['personal', 'business', 'education'][rand(0, 2)],
                    'amount' => $loanAmount,
                    'interest_rate' => rand(8, 15),
                    'term_months' => rand(12, 60),
                    'payment_frequency' => 'monthly',
                    'disbursement_date' => now()->subDays(rand(1, 90)),
                    'due_date' => now()->addMonths(rand(6, 24)),
                    'status' => ['active', 'overdue'][rand(0, 1)],
                    'outstanding_balance' => $loanAmount * (rand(50, 95) / 100),
                    'total_paid' => $loanAmount * (rand(5, 50) / 100),
                    'penalty_rate' => 2.0,
                    'notes' => 'Sample loan for demonstration',
                    'created_by' => 1,
                ]);

                // Create transactions for the loan
                Transaction::create([
                    'transaction_number' => 'TXN-' . str_pad($client->id, 8, '0', STR_PAD_LEFT),
                    'type' => 'loan_disbursement',
                    'amount' => $loanAmount,
                    'currency' => 'USD',
                    'status' => 'completed',
                    'description' => 'Loan disbursement for ' . $client->first_name . ' ' . $client->last_name,
                    'client_id' => $client->id,
                    'loan_id' => $loan->id,
                    'branch_id' => $branch->id,
                    'created_by' => 1,
                    'processed_at' => now(),
                ]);
            }

            // Create some random transactions
            for ($i = 0; $i < rand(2, 5); $i++) {
                Transaction::create([
                    'transaction_number' => 'TXN-' . str_pad($client->id . $i, 8, '0', STR_PAD_LEFT),
                    'type' => ['deposit', 'withdrawal', 'transfer'][rand(0, 2)],
                    'amount' => rand(100, 2000),
                    'currency' => 'USD',
                    'status' => 'completed',
                    'description' => 'Sample transaction ' . ($i + 1),
                    'client_id' => $client->id,
                    'branch_id' => $branch->id,
                    'created_by' => 1,
                    'processed_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}
