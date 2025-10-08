<?php

namespace Database\Seeders;

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
            // ASSETS - 1000-1999
            [
                'code' => '1000',
                'name' => 'Cash on Hand',
                'type' => 'asset',
                'category' => 'cash_on_hand',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Physical cash held in the office',
            ],
            [
                'code' => '1100',
                'name' => 'Cash in Bank',
                'type' => 'asset',
                'category' => 'cash_in_bank',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Cash held in company bank accounts',
            ],
            [
                'code' => '1200',
                'name' => 'Loan Portfolio',
                'type' => 'asset',
                'category' => 'loan_portfolio',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Total outstanding loans to borrowers',
            ],
            [
                'code' => '1300',
                'name' => 'Accounts Receivable',
                'type' => 'asset',
                'category' => 'accounts_receivable',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Amounts owed by borrowers and other debtors',
            ],
            [
                'code' => '1400',
                'name' => 'Property, Plant and Equipment',
                'type' => 'asset',
                'category' => 'property_plant_equipment',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Fixed assets including land, furniture, equipment, vehicles',
            ],
            [
                'code' => '1410',
                'name' => 'Land',
                'type' => 'asset',
                'category' => 'property_plant_equipment',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Land owned by the company',
                'parent_id' => null, // Will be set after parent is created
            ],
            [
                'code' => '1420',
                'name' => 'Furniture and Fixtures',
                'type' => 'asset',
                'category' => 'property_plant_equipment',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Office furniture and fixtures',
                'parent_id' => null, // Will be set after parent is created
            ],
            [
                'code' => '1430',
                'name' => 'Equipment',
                'type' => 'asset',
                'category' => 'property_plant_equipment',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Office equipment and machinery',
                'parent_id' => null, // Will be set after parent is created
            ],
            [
                'code' => '1440',
                'name' => 'Vehicles',
                'type' => 'asset',
                'category' => 'property_plant_equipment',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Company vehicles',
                'parent_id' => null, // Will be set after parent is created
            ],
            [
                'code' => '1500',
                'name' => 'Accumulated Depreciation',
                'type' => 'asset',
                'category' => 'accumulated_depreciation',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Accumulated depreciation on fixed assets',
            ],

            // LIABILITIES - 2000-2999
            [
                'code' => '2000',
                'name' => 'Client Savings',
                'type' => 'liability',
                'category' => 'client_savings',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Total savings deposits from clients',
            ],
            [
                'code' => '2100',
                'name' => 'Interest Payable',
                'type' => 'liability',
                'category' => 'interest_payable',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Interest owed to depositors',
            ],
            [
                'code' => '2200',
                'name' => 'Accounts Payable',
                'type' => 'liability',
                'category' => 'accounts_payable',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Amounts owed to vendors and suppliers',
            ],
            [
                'code' => '2300',
                'name' => 'Loan from Shareholders',
                'type' => 'liability',
                'category' => 'loan_from_shareholders',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Loans received from shareholders',
            ],

            // OWNER'S EQUITY - 3000-3999
            [
                'code' => '3000',
                'name' => 'Capital',
                'type' => 'equity',
                'category' => 'capital',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Owner\'s capital investment',
            ],
            [
                'code' => '3100',
                'name' => 'Net Income',
                'type' => 'equity',
                'category' => 'net_income',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Current period net income',
            ],
            [
                'code' => '3200',
                'name' => 'Retained Earnings',
                'type' => 'equity',
                'category' => 'retained_earnings',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Accumulated retained earnings',
            ],

            // INCOME - 4000-4999
            [
                'code' => '4000',
                'name' => 'Loan Interest Income',
                'type' => 'revenue',
                'category' => 'loan_interest_income',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Interest earned from loans',
            ],
            [
                'code' => '4100',
                'name' => 'Penalty Income',
                'type' => 'revenue',
                'category' => 'penalty_income',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Penalty fees from overdue loans',
            ],
            [
                'code' => '4200',
                'name' => 'Service Fees',
                'type' => 'revenue',
                'category' => 'service_fees',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Service fees from depositors and borrowers',
            ],
            [
                'code' => '4900',
                'name' => 'Other Income',
                'type' => 'revenue',
                'category' => 'other_income',
                'normal_balance' => 'credit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Other miscellaneous income',
            ],

            // EXPENSES - 5000-5999
            [
                'code' => '5000',
                'name' => 'Salaries and Wages',
                'type' => 'expense',
                'category' => 'salaries_wages',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Staff salaries and wages',
            ],
            [
                'code' => '5100',
                'name' => 'Rent Expense',
                'type' => 'expense',
                'category' => 'rent_expense',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Office rent and lease expenses',
            ],
            [
                'code' => '5200',
                'name' => 'Communication and Internet',
                'type' => 'expense',
                'category' => 'communication_internet',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Phone, internet, and communication expenses',
            ],
            [
                'code' => '5300',
                'name' => 'Legal Fees',
                'type' => 'expense',
                'category' => 'legal_fees',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Legal and professional fees',
            ],
            [
                'code' => '5400',
                'name' => 'Subscription Fees',
                'type' => 'expense',
                'category' => 'subscription_fees',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Software subscriptions and licensing fees',
            ],
            [
                'code' => '5500',
                'name' => 'Utilities',
                'type' => 'expense',
                'category' => 'utilities',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Electricity, water, and other utilities',
            ],
            [
                'code' => '5600',
                'name' => 'Depreciation Expense',
                'type' => 'expense',
                'category' => 'depreciation_expense',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Depreciation on fixed assets',
            ],
            [
                'code' => '5700',
                'name' => 'Loan Loss Expense',
                'type' => 'expense',
                'category' => 'loan_loss_expense',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => true,
                'description' => 'Provision for loan losses',
            ],
            [
                'code' => '5900',
                'name' => 'Other Expenses',
                'type' => 'expense',
                'category' => 'other_expenses',
                'normal_balance' => 'debit',
                'opening_balance' => 0,
                'is_system_account' => false,
                'description' => 'Other miscellaneous expenses',
            ],
        ];

        // Create accounts
        foreach ($accounts as $accountData) {
            $account = ChartOfAccount::create($accountData);
            
            // Set parent relationships for PPE sub-accounts
            if (in_array($account->code, ['1410', '1420', '1430', '1440'])) {
                $parentAccount = ChartOfAccount::where('code', '1400')->first();
                if ($parentAccount) {
                    $account->parent_id = $parentAccount->id;
                    $account->save();
                }
            }
        }
    }
}