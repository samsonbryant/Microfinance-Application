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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
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
            ]);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_system_account')->default(false);
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
            $table->index(['type', 'is_active']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
