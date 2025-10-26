<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;
use App\Models\ChartOfAccount;

class BanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankAccount = ChartOfAccount::where('code', '1100')->first();

        $banks = [
            [
                'name' => 'Cash on Hand',
                'type' => 'cash',
                'account_id' => ChartOfAccount::where('code', '1000')->first()?->id,
                'current_balance' => 10000.00,
                'is_active' => true,
                'description' => 'Main cash account',
            ],
            [
                'name' => 'BnB (Bank of Nowhere and Beyond)',
                'type' => 'bank',
                'account_id' => $bankAccount?->id,
                'account_number' => '100234567890',
                'swift_code' => 'BNBNLR01',
                'branch_name' => 'Main Branch',
                'address' => '123 Main Street, Capital City',
                'contact_person' => 'John Banker',
                'phone' => '+231-777-123-456',
                'email' => 'bnb@example.com',
                'current_balance' => 0,
                'is_active' => true,
                'description' => 'Primary banking partner',
            ],
            [
                'name' => 'GT Bank',
                'type' => 'bank',
                'account_id' => $bankAccount?->id,
                'account_number' => '200987654321',
                'swift_code' => 'GTBNLR02',
                'branch_name' => 'Central Branch',
                'address' => '456 Central Ave, Capital City',
                'contact_person' => 'Jane Manager',
                'phone' => '+231-777-234-567',
                'email' => 'gtbank@example.com',
                'current_balance' => 0,
                'is_active' => true,
                'description' => 'Secondary bank account',
            ],
            [
                'name' => 'Eco Bank',
                'type' => 'bank',
                'account_id' => $bankAccount?->id,
                'account_number' => '300456789012',
                'swift_code' => 'ECBNLR03',
                'branch_name' => 'Downtown Branch',
                'address' => '789 Commerce St, Capital City',
                'contact_person' => 'Peter Finance',
                'phone' => '+231-777-345-678',
                'email' => 'ecobank@example.com',
                'current_balance' => 0,
                'is_active' => true,
                'description' => 'Eco Bank business account',
            ],
            [
                'name' => 'IB (International Bank)',
                'type' => 'bank',
                'account_id' => $bankAccount?->id,
                'account_number' => '400654321098',
                'swift_code' => 'IBNKLR04',
                'branch_name' => 'International Branch',
                'address' => '321 Global Plaza, Capital City',
                'contact_person' => 'Sarah International',
                'phone' => '+231-777-456-789',
                'email' => 'ib@example.com',
                'current_balance' => 0,
                'is_active' => true,
                'description' => 'International transactions',
            ],
            [
                'name' => 'LBDI (Liberian Bank for Development)',
                'type' => 'bank',
                'account_id' => $bankAccount?->id,
                'account_number' => '500123456789',
                'swift_code' => 'LBDILR05',
                'branch_name' => 'Development Branch',
                'address' => '555 Development Rd, Capital City',
                'contact_person' => 'Michael Developer',
                'phone' => '+231-777-567-890',
                'email' => 'lbdi@example.com',
                'current_balance' => 0,
                'is_active' => true,
                'description' => 'Development projects account',
            ],
            [
                'name' => 'UBA (United Bank of Africa)',
                'type' => 'bank',
                'account_id' => $bankAccount?->id,
                'account_number' => '600987654321',
                'swift_code' => 'UBANLR06',
                'branch_name' => 'Unity Branch',
                'address' => '777 Unity Blvd, Capital City',
                'contact_person' => 'Grace Unity',
                'phone' => '+231-777-678-901',
                'email' => 'uba@example.com',
                'current_balance' => 0,
                'is_active' => true,
                'description' => 'UBA business account',
            ],
            [
                'name' => 'Orange Money',
                'type' => 'mobile_money',
                'account_id' => $bankAccount?->id,
                'account_number' => '0777-123-456',
                'phone' => '+231-777-123-456',
                'current_balance' => 0,
                'is_active' => true,
                'description' => 'Mobile money transactions',
            ],
            [
                'name' => 'Mobile Money',
                'type' => 'mobile_money',
                'account_id' => $bankAccount?->id,
                'account_number' => '0777-234-567',
                'phone' => '+231-777-234-567',
                'current_balance' => 0,
                'is_active' => true,
                'description' => 'Generic mobile money account',
            ],
        ];

        foreach ($banks as $bank) {
            Bank::create($bank);
        }

        $this->command->info('Banks seeded successfully!');
    }
}

