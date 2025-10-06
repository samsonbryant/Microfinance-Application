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
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_name');
            $table->enum('workflow_type', ['loan_application', 'loan_disbursement', 'loan_writeoff', 'collateral_release', 'client_approval'])->default('loan_application');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            
            // Workflow Configuration
            $table->integer('approval_level')->default(1); // 1 = Single level, 2 = Two level, etc.
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium');
            
            // Approval Requirements
            $table->boolean('requires_loan_officer_approval')->default(true);
            $table->boolean('requires_branch_manager_approval')->default(true);
            $table->boolean('requires_compliance_approval')->default(false);
            $table->boolean('requires_head_office_approval')->default(false);
            $table->boolean('requires_board_approval')->default(false);
            
            // Approval Limits
            $table->decimal('loan_officer_limit', 15, 2)->default(0);
            $table->decimal('branch_manager_limit', 15, 2)->default(0);
            $table->decimal('compliance_limit', 15, 2)->default(0);
            $table->decimal('head_office_limit', 15, 2)->default(0);
            $table->decimal('board_limit', 15, 2)->default(0);
            
            // Workflow Status
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('approval_matrix')->nullable(); // Store complex approval rules
            
            $table->timestamps();
            
            // Indexes
            $table->index(['workflow_type', 'branch_id', 'is_active']);
            $table->index(['risk_level', 'min_amount', 'max_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_workflows');
    }
};