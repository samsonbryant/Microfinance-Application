<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the table with the nullable column
        // First, let's just update the existing records and modify the constraint
        
        // Drop the foreign key constraint
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropForeign(['loan_officer_id']);
        });

        // For SQLite, we need to use raw SQL to modify the column
        // We'll create a temporary table, copy data, drop old, rename new
        if (DB::getDriverName() === 'sqlite') {
            // Get all data
            $applications = DB::table('loan_applications')->get();
            
            // Drop the old table
            Schema::dropIfExists('loan_applications');
            
            // Recreate with nullable loan_officer_id
            Schema::create('loan_applications', function (Blueprint $table) {
                $table->id();
                $table->string('application_number')->unique();
                $table->foreignId('client_id')->constrained()->onDelete('cascade');
                $table->foreignId('branch_id')->constrained()->onDelete('cascade');
                $table->foreignId('loan_officer_id')->nullable()->constrained('users')->onDelete('set null');
                
                // Loan Details
                $table->enum('loan_type', ['personal', 'business', 'agricultural', 'education', 'housing', 'micro', 'group', 'emergency'])->default('personal');
                $table->decimal('requested_amount', 15, 2);
                $table->decimal('approved_amount', 15, 2)->nullable();
                $table->integer('requested_term_months');
                $table->integer('approved_term_months')->nullable();
                $table->decimal('requested_interest_rate', 5, 2);
                $table->decimal('approved_interest_rate', 5, 2)->nullable();
                $table->enum('payment_frequency', ['weekly', 'biweekly', 'monthly', 'quarterly', 'lump_sum'])->default('monthly');
                
                // Additional columns
                $table->integer('term_months')->nullable();
                $table->decimal('interest_rate', 5, 2)->nullable();
                $table->text('purpose')->nullable();
                $table->string('employment_status')->nullable();
                $table->string('collateral_type')->nullable();
                
                // Purpose and Details
                $table->text('loan_purpose');
                $table->text('business_description')->nullable();
                $table->decimal('monthly_income', 15, 2)->nullable();
                $table->decimal('monthly_expenses', 15, 2)->nullable();
                $table->text('collateral_description')->nullable();
                $table->decimal('collateral_value', 15, 2)->nullable();
                
                // Application Status and Workflow
                $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'disbursed', 'cancelled', 'pending'])->default('draft');
                $table->enum('kyc_status', ['pending', 'verified', 'rejected'])->default('pending');
                $table->enum('credit_check_status', ['pending', 'passed', 'failed'])->default('pending');
                $table->decimal('credit_score', 5, 2)->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('notes')->nullable();
                
                // Approval Workflow
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('rejected_at')->nullable();
                
                // Risk Assessment
                $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium');
                $table->text('risk_assessment_notes')->nullable();
                $table->decimal('ltv_ratio', 5, 2)->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->index(['status', 'created_at']);
                $table->index(['client_id', 'status']);
                $table->index(['branch_id', 'status']);
            });
            
            // Restore data
            foreach ($applications as $app) {
                DB::table('loan_applications')->insert((array)$app);
            }
        } else {
            // For MySQL/PostgreSQL
            Schema::table('loan_applications', function (Blueprint $table) {
                $table->foreignId('loan_officer_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Reverse is complex for SQLite, so we'll skip it
        // In production, you'd want to handle this properly
    }
};
