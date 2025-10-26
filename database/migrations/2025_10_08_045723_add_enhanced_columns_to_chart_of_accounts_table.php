<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            // Add the missing columns
            $table->enum('category', [
                // Assets
                'cash_on_hand', 'cash_in_bank', 'accounts_receivable', 'loan_portfolio', 'property_plant_equipment', 'accumulated_depreciation',
                // Liabilities  
                'client_savings', 'interest_payable', 'accounts_payable', 'loan_from_shareholders',
                // Owner's Equity
                'capital', 'net_income', 'retained_earnings',
                // Income
                'loan_interest_income', 'penalty_income', 'service_fees', 'other_income',
                // Expenses
                'salaries_wages', 'rent_expense', 'communication_internet', 'legal_fees', 'subscription_fees', 
                'utilities', 'depreciation_expense', 'loan_loss_expense', 'other_expenses'
            ])->nullable()->after('type');
            
            $table->decimal('opening_balance', 15, 2)->default(0)->after('normal_balance');
            $table->string('currency', 3)->default('USD')->after('opening_balance');
            $table->boolean('is_system_account')->default(false)->after('currency');
            
            // Add indexes
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'opening_balance', 'currency', 'is_system_account']);
        });
    }
};
