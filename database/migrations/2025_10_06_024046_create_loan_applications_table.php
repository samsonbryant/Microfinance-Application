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
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_officer_id')->constrained('users')->onDelete('cascade');
            
            // Loan Details
            $table->enum('loan_type', ['personal', 'business', 'agricultural', 'education', 'housing', 'micro', 'group', 'emergency'])->default('personal');
            $table->decimal('requested_amount', 15, 2);
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->integer('requested_term_months');
            $table->integer('approved_term_months')->nullable();
            $table->decimal('requested_interest_rate', 5, 2);
            $table->decimal('approved_interest_rate', 5, 2)->nullable();
            $table->enum('payment_frequency', ['weekly', 'biweekly', 'monthly', 'quarterly', 'lump_sum'])->default('monthly');
            
            // Purpose and Details
            $table->text('loan_purpose');
            $table->text('business_description')->nullable();
            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->decimal('monthly_expenses', 15, 2)->nullable();
            $table->text('collateral_description')->nullable();
            $table->decimal('collateral_value', 15, 2)->nullable();
            
            // Application Status and Workflow
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'disbursed', 'cancelled'])->default('draft');
            $table->enum('kyc_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->enum('credit_check_status', ['pending', 'passed', 'failed'])->default('pending');
            $table->decimal('credit_score', 5, 2)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            
            // Approval Workflow
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            
            // Risk Assessment
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium');
            $table->text('risk_assessment_notes')->nullable();
            $table->decimal('ltv_ratio', 5, 2)->nullable(); // Loan-to-Value ratio
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['client_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['loan_officer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};