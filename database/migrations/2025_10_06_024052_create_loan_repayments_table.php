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
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->string('repayment_number')->unique();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            
            // Repayment Details
            $table->date('due_date');
            $table->date('actual_payment_date')->nullable();
            $table->decimal('scheduled_amount', 15, 2);
            $table->decimal('principal_amount', 15, 2)->default(0);
            $table->decimal('interest_amount', 15, 2)->default(0);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->decimal('fees_amount', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2);
            
            // Payment Information
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mobile_money', 'cheque', 'direct_debit'])->default('cash');
            $table->string('payment_reference')->nullable();
            $table->string('receipt_number')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'partial', 'waived'])->default('pending');
            $table->text('notes')->nullable();
            
            // Late Payment Details
            $table->integer('days_overdue')->default(0);
            $table->decimal('penalty_rate', 5, 2)->default(0);
            $table->timestamp('penalty_applied_at')->nullable();
            
            // Processing Information
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            
            // Early Payment Incentive
            $table->boolean('is_early_payment')->default(false);
            $table->decimal('early_payment_discount', 15, 2)->default(0);
            
            // Rescheduling Information
            $table->boolean('is_rescheduled')->default(false);
            $table->date('original_due_date')->nullable();
            $table->text('reschedule_reason')->nullable();
            $table->foreignId('rescheduled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rescheduled_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['loan_id', 'due_date']);
            $table->index(['client_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['status', 'days_overdue']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};