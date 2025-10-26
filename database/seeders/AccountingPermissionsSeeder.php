<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AccountingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create accounting permissions
        $permissions = [
            // General accounting permissions
            'view_accounting',
            'view_financial_reports',
            'export_financial_reports',
            
            // Chart of accounts permissions
            'manage_chart_of_accounts',
            'view_chart_of_accounts',
            
            // General ledger permissions
            'view_general_ledger',
            'post_general_ledger',
            
            // Journal entry permissions
            'manage_journal_entries',
            'view_journal_entries',
            'create_journal_entries',
            'edit_journal_entries',
            'approve_journal_entries',
            'post_journal_entries',
            
            // Expense entry permissions
            'manage_expenses',
            'view_expenses',
            'create_expenses',
            'edit_expenses',
            'approve_expenses',
            'post_expenses',
            
            // Reconciliation permissions
            'manage_reconciliations',
            'view_reconciliations',
            'approve_reconciliations',
            
            // Reporting permissions
            'view_profit_loss',
            'view_balance_sheet',
            'view_trial_balance',
            'view_cash_flow',
            'view_loan_portfolio_aging',
            'view_provisioning_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Get existing roles
        $adminRole = Role::where('name', 'admin')->first();
        $generalManagerRole = Role::where('name', 'general_manager')->first();
        $branchManagerRole = Role::where('name', 'branch_manager')->first();
        $loanOfficerRole = Role::where('name', 'loan_officer')->first();
        $accountantRole = Role::where('name', 'accountant')->first();
        $tellerRole = Role::where('name', 'teller')->first();

        // Create accountant role if it doesn't exist
        if (!$accountantRole) {
            $accountantRole = Role::create(['name' => 'accountant']);
        }

        // Create teller role if it doesn't exist
        if (!$tellerRole) {
            $tellerRole = Role::create(['name' => 'teller']);
        }

        // Admin - Full accounting access
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'view_accounting',
                'view_financial_reports',
                'export_financial_reports',
                'manage_chart_of_accounts',
                'view_chart_of_accounts',
                'view_general_ledger',
                'post_general_ledger',
                'manage_journal_entries',
                'view_journal_entries',
                'create_journal_entries',
                'edit_journal_entries',
                'approve_journal_entries',
                'post_journal_entries',
                'manage_expenses',
                'view_expenses',
                'create_expenses',
                'edit_expenses',
                'approve_expenses',
                'post_expenses',
                'manage_reconciliations',
                'view_reconciliations',
                'approve_reconciliations',
                'view_profit_loss',
                'view_balance_sheet',
                'view_trial_balance',
                'view_cash_flow',
                'view_loan_portfolio_aging',
                'view_provisioning_reports',
            ]);
        }

        // General Manager - High-level accounting access
        if ($generalManagerRole) {
            $generalManagerRole->givePermissionTo([
                'view_accounting',
                'view_financial_reports',
                'export_financial_reports',
                'view_chart_of_accounts',
                'view_general_ledger',
                'view_journal_entries',
                'approve_journal_entries',
                'post_journal_entries',
                'view_expenses',
                'approve_expenses',
                'post_expenses',
                'view_reconciliations',
                'approve_reconciliations',
                'view_profit_loss',
                'view_balance_sheet',
                'view_trial_balance',
                'view_cash_flow',
                'view_loan_portfolio_aging',
                'view_provisioning_reports',
            ]);
        }

        // Branch Manager - Branch-level accounting access
        if ($branchManagerRole) {
            $branchManagerRole->givePermissionTo([
                'view_accounting',
                'view_financial_reports',
                'view_chart_of_accounts',
                'view_general_ledger',
                'view_journal_entries',
                'approve_journal_entries',
                'view_expenses',
                'create_expenses',
                'approve_expenses',
                'view_reconciliations',
                'view_profit_loss',
                'view_balance_sheet',
                'view_trial_balance',
            ]);
        }

        // Accountant - Full accounting operations
        if ($accountantRole) {
            $accountantRole->givePermissionTo([
                'view_accounting',
                'view_financial_reports',
                'export_financial_reports',
                'manage_chart_of_accounts',
                'view_chart_of_accounts',
                'view_general_ledger',
                'post_general_ledger',
                'manage_journal_entries',
                'view_journal_entries',
                'create_journal_entries',
                'edit_journal_entries',
                'post_journal_entries',
                'manage_expenses',
                'view_expenses',
                'create_expenses',
                'edit_expenses',
                'post_expenses',
                'manage_reconciliations',
                'view_reconciliations',
                'view_profit_loss',
                'view_balance_sheet',
                'view_trial_balance',
                'view_cash_flow',
                'view_loan_portfolio_aging',
                'view_provisioning_reports',
            ]);
        }

        // Loan Officer - Limited accounting access
        if ($loanOfficerRole) {
            $loanOfficerRole->givePermissionTo([
                'view_accounting',
                'view_financial_reports',
                'view_chart_of_accounts',
                'view_general_ledger',
                'view_journal_entries',
                'view_expenses',
                'create_expenses',
                'view_reconciliations',
            ]);
        }

        // Teller - Basic accounting access
        if ($tellerRole) {
            $tellerRole->givePermissionTo([
                'view_accounting',
                'view_chart_of_accounts',
                'view_general_ledger',
                'view_journal_entries',
                'view_expenses',
            ]);
        }
    }
}
