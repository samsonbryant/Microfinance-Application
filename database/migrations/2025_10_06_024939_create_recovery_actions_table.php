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
        Schema::create('recovery_actions', function (Blueprint $table) {
            $table->id();
            $table->string('action_number')->unique();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            
            // Action Details
            $table->enum('action_type', [
                'collateral_repossession',
                'legal_notice',
                'court_filing',
                'debt_restructuring',
                'write_off',
                'external_collection',
                'asset_attachment',
                'bankruptcy_proceedings'
            ]);
            
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Financial Details
            $table->decimal('outstanding_amount', 15, 2);
            $table->decimal('recovered_amount', 15, 2)->default(0);
            $table->decimal('legal_costs', 15, 2)->default(0);
            $table->decimal('collection_costs', 15, 2)->default(0);
            $table->decimal('net_recovery', 15, 2)->default(0);
            
            // Action Details
            $table->text('action_description');
            $table->text('legal_basis')->nullable();
            $table->text('required_documents')->nullable();
            $table->json('attachments')->nullable();
            
            // Timeline
            $table->date('action_date');
            $table->date('expected_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->integer('days_to_complete')->nullable();
            
            // Legal Information
            $table->string('court_case_number')->nullable();
            $table->string('lawyer_name')->nullable();
            $table->string('lawyer_contact')->nullable();
            $table->decimal('legal_fees', 15, 2)->default(0);
            
            // Collateral Information
            $table->foreignId('collateral_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('collateral_value', 15, 2)->nullable();
            $table->enum('collateral_status', ['in_custody', 'repossessed', 'sold', 'returned'])->nullable();
            $table->date('collateral_repossession_date')->nullable();
            $table->date('collateral_sale_date')->nullable();
            $table->decimal('collateral_sale_amount', 15, 2)->nullable();
            
            // External Collection
            $table->string('collection_agency')->nullable();
            $table->string('agency_contact')->nullable();
            $table->decimal('agency_fee_percentage', 5, 2)->nullable();
            $table->decimal('agency_fee_amount', 15, 2)->nullable();
            
            // Results
            $table->enum('outcome', [
                'full_recovery',
                'partial_recovery',
                'no_recovery',
                'settlement',
                'write_off',
                'ongoing'
            ])->nullable();
            
            $table->text('outcome_notes')->nullable();
            $table->text('lessons_learned')->nullable();
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Follow-up
            $table->boolean('requires_follow_up')->default(false);
            $table->date('next_review_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['loan_id', 'status']);
            $table->index(['client_id', 'action_type']);
            $table->index(['assigned_to', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['action_type', 'priority']);
            $table->index(['action_date', 'status']);
            $table->index(['outcome', 'action_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_actions');
    }
};