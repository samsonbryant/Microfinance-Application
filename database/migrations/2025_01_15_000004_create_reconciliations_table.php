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
        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->string('reconciliation_number')->unique();
            $table->enum('type', ['cash', 'bank', 'loan_portfolio', 'savings_portfolio']);
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->date('reconciliation_date');
            $table->decimal('system_balance', 15, 2);
            $table->decimal('actual_balance', 15, 2);
            $table->decimal('variance', 15, 2);
            $table->enum('status', ['draft', 'in_progress', 'completed', 'approved'])->default('draft');
            $table->text('notes')->nullable();
            $table->json('reconciliation_items')->nullable(); // Matched/unmatched items
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'reconciliation_date']);
            $table->index(['branch_id', 'reconciliation_date']);
            $table->index(['status', 'reconciliation_date']);
        });

        Schema::create('reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->constrained('reconciliations')->onDelete('cascade');
            $table->string('reference_type')->nullable(); // general_ledger_entry, bank_statement, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->date('transaction_date');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['unmatched', 'matched', 'disputed'])->default('unmatched');
            $table->string('external_reference')->nullable(); // Bank reference, receipt number, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['reconciliation_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_items');
        Schema::dropIfExists('reconciliations');
    }
};
