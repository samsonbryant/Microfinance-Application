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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('collection_number')->unique();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            
            // Collection Details
            $table->enum('collection_type', ['phone_call', 'sms', 'email', 'visit', 'legal_notice', 'collateral_repossession'])->default('phone_call');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'escalated'])->default('pending');
            
            // Amount Information
            $table->decimal('overdue_amount', 15, 2);
            $table->decimal('collected_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2);
            $table->integer('days_overdue');
            
            // Collection Details
            $table->text('collection_notes')->nullable();
            $table->text('client_response')->nullable();
            $table->date('promised_payment_date')->nullable();
            $table->decimal('promised_amount', 15, 2)->nullable();
            
            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('next_follow_up')->nullable();
            
            // Escalation
            $table->boolean('is_escalated')->default(false);
            $table->foreignId('escalated_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('escalated_at')->nullable();
            $table->text('escalation_reason')->nullable();
            
            // Results
            $table->enum('outcome', [
                'payment_received', 
                'payment_promised', 
                'no_response', 
                'refused_to_pay', 
                'client_unreachable',
                'requires_legal_action',
                'collateral_repossession'
            ])->nullable();
            
            $table->text('outcome_notes')->nullable();
            $table->json('attachments')->nullable(); // Store file paths
            
            // Follow-up
            $table->boolean('requires_follow_up')->default(false);
            $table->integer('follow_up_days')->nullable();
            $table->text('follow_up_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['loan_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['collection_type', 'priority']);
            $table->index(['scheduled_at', 'status']);
            $table->index(['days_overdue', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};