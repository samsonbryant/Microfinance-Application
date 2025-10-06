<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Cash and Cash Equivalents', 'type' => 'asset', 'normal_balance' => 'debit', 'description' => 'Cash on hand and in bank accounts'],
            ['code' => '1100', 'name' => 'Cash in Hand', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1000', 'description' => 'Physical cash in office'],
            ['code' => '1200', 'name' => 'Loan Receivables', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1000', 'description' => 'Outstanding loan amounts'],
            ['code' => '1300', 'name' => 'Interest Receivables', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1000', 'description' => 'Accrued interest on loans'],
            ['code' => '1400', 'name' => 'Fixed Assets', 'type' => 'asset', 'normal_balance' => 'debit', 'description' => 'Property, plant and equipment'],
            ['code' => '1500', 'name' => 'Office Equipment', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1400', 'description' => 'Computers, furniture, etc.'],
            
            // Liabilities
            ['code' => '2000', 'name' => 'Savings Liabilities', 'type' => 'liability', 'normal_balance' => 'credit', 'description' => 'Customer savings deposits'],
            ['code' => '2100', 'name' => 'Regular Savings', 'type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2000', 'description' => 'Regular savings accounts'],
            ['code' => '2200', 'name' => 'Fixed Deposits', 'type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2000', 'description' => 'Fixed term deposits'],
            ['code' => '2300', 'name' => 'Accounts Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'description' => 'Amounts owed to suppliers'],
            ['code' => '2400', 'name' => 'Accrued Expenses', 'type' => 'liability', 'normal_balance' => 'credit', 'description' => 'Expenses incurred but not yet paid'],
            
            // Equity
            ['code' => '3000', 'name' => 'Share Capital', 'type' => 'equity', 'normal_balance' => 'credit', 'description' => 'Owner\'s investment in the business'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity', 'normal_balance' => 'credit', 'description' => 'Accumulated profits'],
            ['code' => '3200', 'name' => 'Current Year Profit', 'type' => 'equity', 'normal_balance' => 'credit', 'description' => 'Profit for current year'],
            
            // Revenue
            ['code' => '4000', 'name' => 'Interest Income', 'type' => 'revenue', 'normal_balance' => 'credit', 'description' => 'Interest earned on loans'],
            ['code' => '4100', 'name' => 'Loan Interest', 'type' => 'revenue', 'normal_balance' => 'credit', 'parent_code' => '4000', 'description' => 'Interest from loan disbursements'],
            ['code' => '4200', 'name' => 'Penalty Income', 'type' => 'revenue', 'normal_balance' => 'credit', 'parent_code' => '4000', 'description' => 'Penalties from overdue loans'],
            ['code' => '4300', 'name' => 'Service Charges', 'type' => 'revenue', 'normal_balance' => 'credit', 'description' => 'Fees and service charges'],
            ['code' => '4400', 'name' => 'Other Income', 'type' => 'revenue', 'normal_balance' => 'credit', 'description' => 'Miscellaneous income'],
            
            // Expenses
            ['code' => '5000', 'name' => 'Operating Expenses', 'type' => 'expense', 'normal_balance' => 'debit', 'description' => 'General operating expenses'],
            ['code' => '5100', 'name' => 'Salaries and Wages', 'type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000', 'description' => 'Staff salaries and wages'],
            ['code' => '5200', 'name' => 'Rent and Utilities', 'type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000', 'description' => 'Office rent and utility bills'],
            ['code' => '5300', 'name' => 'Professional Fees', 'type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000', 'description' => 'Legal, audit, and consulting fees'],
            ['code' => '5400', 'name' => 'Depreciation', 'type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000', 'description' => 'Depreciation on fixed assets'],
            ['code' => '5500', 'name' => 'Interest Expense', 'type' => 'expense', 'normal_balance' => 'debit', 'description' => 'Interest paid on borrowings'],
            ['code' => '5600', 'name' => 'Bad Debt Expense', 'type' => 'expense', 'normal_balance' => 'debit', 'description' => 'Provision for bad debts'],
        ];

        foreach ($accounts as $accountData) {
            $parentId = null;
            if (isset($accountData['parent_code'])) {
                $parent = ChartOfAccount::where('code', $accountData['parent_code'])->first();
                if ($parent) {
                    $parentId = $parent->id;
                }
            }

            ChartOfAccount::create([
                'code' => $accountData['code'],
                'name' => $accountData['name'],
                'type' => $accountData['type'],
                'parent_id' => $parentId,
                'normal_balance' => $accountData['normal_balance'],
                'description' => $accountData['description'],
                'is_active' => true,
            ]);
        }
    }
}
