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
        Schema::create('client_risk_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            
            // Risk Assessment Scores
            $table->decimal('credit_score', 5, 2)->default(0); // 0-100
            $table->decimal('risk_score', 5, 2)->default(0); // 0-100
            $table->enum('risk_level', ['low', 'medium', 'high', 'very_high'])->default('medium');
            
            // Financial Assessment
            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->decimal('monthly_expenses', 15, 2)->nullable();
            $table->decimal('debt_to_income_ratio', 5, 2)->nullable();
            $table->decimal('net_worth', 15, 2)->nullable();
            
            // Credit History
            $table->integer('total_loans_taken')->default(0);
            $table->integer('loans_repaid_on_time')->default(0);
            $table->integer('loans_overdue')->default(0);
            $table->integer('loans_defaulted')->default(0);
            $table->decimal('average_loan_amount', 15, 2)->default(0);
            $table->decimal('total_interest_paid', 15, 2)->default(0);
            
            // Business Assessment (for business loans)
            $table->integer('business_years')->nullable();
            $table->decimal('business_revenue', 15, 2)->nullable();
            $table->decimal('business_profit', 15, 2)->nullable();
            $table->enum('business_stability', ['stable', 'growing', 'declining', 'volatile'])->nullable();
            
            // Collateral Assessment
            $table->decimal('collateral_value', 15, 2)->default(0);
            $table->decimal('collateral_ltv_ratio', 5, 2)->default(0);
            $table->enum('collateral_quality', ['excellent', 'good', 'fair', 'poor'])->nullable();
            
            // External Factors
            $table->enum('employment_status', ['employed', 'self_employed', 'unemployed', 'retired'])->nullable();
            $table->integer('employment_years')->nullable();
            $table->string('employer_name')->nullable();
            $table->enum('industry_risk', ['low', 'medium', 'high'])->nullable();
            
            // Behavioral Factors
            $table->integer('account_age_months')->default(0);
            $table->integer('transaction_frequency')->default(0);
            $table->decimal('average_transaction_amount', 15, 2)->default(0);
            $table->boolean('has_savings_history')->default(false);
            $table->boolean('has_overdraft_history')->default(false);
            
            // Risk Flags
            $table->boolean('is_pep')->default(false); // Politically Exposed Person
            $table->boolean('is_sanctioned')->default(false);
            $table->boolean('has_criminal_record')->default(false);
            $table->boolean('is_bankrupt')->default(false);
            $table->text('risk_flags_notes')->nullable();
            
            // Assessment Details
            $table->text('assessment_notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->foreignId('assessed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('assessment_date')->useCurrent();
            $table->timestamp('last_updated')->nullable();
            
            // Approval Information
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('approval_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['client_id', 'risk_level']);
            $table->index(['branch_id', 'risk_level']);
            $table->index(['risk_score', 'risk_level']);
            $table->index(['assessment_date', 'risk_level']);
            $table->index(['assessed_by', 'assessment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_risk_profiles');
    }
};