<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BorrowerTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test borrower user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'borrower@test.com'],
            [
                'name' => 'Test Borrower',
                'username' => 'test_borrower_' . rand(1000, 9999),
                'password' => bcrypt('password'),
                'role' => 'borrower',
                'is_active' => true,
            ]
        );

        // Assign borrower role
        if (!$user->hasRole('borrower')) {
            $user->assignRole('borrower');
        }

        // Create a test client record linked to the user
        $client = \App\Models\Client::firstOrCreate(
            ['user_id' => $user->id],
            [
                'client_number' => 'CLI-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'first_name' => 'Test',
                'last_name' => 'Borrower',
                'email' => 'borrower@test.com',
                'phone' => '+1234567890',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'marital_status' => 'single',
                'identification_type' => 'national_id',
                'identification_number' => 'ID123456789',
                'address' => '123 Test Street',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'Test Country',
                'occupation' => 'Software Developer',
                'employer' => 'Test Company',
                'monthly_income' => 5000.00,
                'income_currency' => 'USD',
                'kyc_status' => 'verified',
                'status' => 'active',
                'branch_id' => 1,
                'created_by' => 1,
            ]
        );

        // Create a test loan for the borrower
        $loan = \App\Models\Loan::firstOrCreate(
            [
                'client_id' => $client->id,
                'loan_number' => 'LOAN-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            ],
            [
                'amount' => 10000.00,
                'interest_rate' => 12.00,
                'term_months' => 12,
                'monthly_payment' => 888.49,
                'status' => 'disbursed',
                'disbursement_date' => now()->subDays(30),
                'next_payment_date' => now()->addDays(15),
                'outstanding_balance' => 8000.00,
                'branch_id' => 1,
                'created_by' => 1,
            ]
        );

        // Create a test savings account for the borrower
        $savingsAccount = \App\Models\SavingsAccount::firstOrCreate(
            [
                'client_id' => $client->id,
                'account_number' => 'SAV-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            ],
            [
                'account_type' => 'regular',
                'balance' => 2500.00,
                'interest_rate' => 3.00,
                'status' => 'active',
                'branch_id' => 1,
                'created_by' => 1,
            ]
        );

        $this->command->info('Test borrower data created successfully!');
        $this->command->info('Email: borrower@test.com');
        $this->command->info('Password: password');
    }
}
