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
        Schema::create('expense_entries', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->date('expense_date');
            $table->decimal('amount', 15, 2);
            $table->string('reference_number')->nullable();
            $table->text('description');
            $table->enum('status', ['pending', 'approved', 'rejected', 'posted'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('receipt_number')->nullable();
            $table->json('attachments')->nullable(); // File paths for receipts
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['branch_id', 'expense_date']);
            $table->index(['account_id', 'expense_date']);
            $table->index(['status', 'expense_date']);
            $table->index('expense_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_entries');
    }
};
