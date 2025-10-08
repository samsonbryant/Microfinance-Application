<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Loan Details
            if (!Schema::hasColumn('loans', 'principal_amount')) {
                $table->decimal('principal_amount', 15, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('loans', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('amount');
            }
            if (!Schema::hasColumn('loans', 'release_date')) {
                $table->date('release_date')->nullable()->after('disbursement_date');
            }
            if (!Schema::hasColumn('loans', 'duration_period')) {
                $table->enum('duration_period', ['days', 'weeks', 'months', 'years'])->default('months')->after('term_months');
            }
            
            // Interest Details
            if (!Schema::hasColumn('loans', 'interest_method')) {
                $table->enum('interest_method', ['flat', 'declining_balance', 'compound'])->default('flat')->after('interest_rate');
            }
            if (!Schema::hasColumn('loans', 'interest_cycle')) {
                $table->enum('interest_cycle', ['once', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly')->after('interest_method');
            }
            
            // Repayment Details
            if (!Schema::hasColumn('loans', 'repayment_type')) {
                $table->enum('repayment_type', ['standard', 'balloon', 'interest_only', 'custom'])->default('standard')->after('payment_frequency');
            }
            if (!Schema::hasColumn('loans', 'repayment_cycle')) {
                $table->enum('repayment_cycle', ['once', 'daily', 'weekly', 'biweekly', 'monthly', 'quarterly'])->default('monthly')->after('repayment_type');
            }
            if (!Schema::hasColumn('loans', 'repayment_days')) {
                $table->json('repayment_days')->nullable()->after('repayment_cycle');
            }
            
            // Penalty
            if (!Schema::hasColumn('loans', 'late_penalty_enabled')) {
                $table->boolean('late_penalty_enabled')->default(false)->after('penalty_rate');
            }
            if (!Schema::hasColumn('loans', 'late_penalty_amount')) {
                $table->decimal('late_penalty_amount', 15, 2)->nullable()->after('late_penalty_enabled');
            }
            if (!Schema::hasColumn('loans', 'late_penalty_type')) {
                $table->enum('late_penalty_type', ['fixed', 'percentage'])->nullable()->after('late_penalty_amount');
            }
            
            // Accounting
            if (!Schema::hasColumn('loans', 'funding_account_id')) {
                $table->foreignId('funding_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('loans', 'loans_receivable_account_id')) {
                $table->foreignId('loans_receivable_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('loans', 'interest_income_account_id')) {
                $table->foreignId('interest_income_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('loans', 'fees_income_account_id')) {
                $table->foreignId('fees_income_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('loans', 'penalty_income_account_id')) {
                $table->foreignId('penalty_income_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('loans', 'overpayment_account_id')) {
                $table->foreignId('overpayment_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            }
            
            // Files
            if (!Schema::hasColumn('loans', 'files')) {
                $table->json('files')->nullable();
            }
            
            // Credit Risk
            if (!Schema::hasColumn('loans', 'credit_risk_score')) {
                $table->decimal('credit_risk_score', 5, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'principal_amount', 'currency', 'release_date', 'duration_period',
                'interest_method', 'interest_cycle',
                'repayment_type', 'repayment_cycle', 'repayment_days',
                'late_penalty_enabled', 'late_penalty_amount', 'late_penalty_type',
                'funding_account_id', 'loans_receivable_account_id', 'interest_income_account_id',
                'fees_income_account_id', 'penalty_income_account_id', 'overpayment_account_id',
                'files', 'credit_risk_score'
            ]);
        });
    }
};
