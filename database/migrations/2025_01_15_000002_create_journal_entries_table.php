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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('journal_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->date('transaction_date');
            $table->text('description');
            $table->string('reference_number')->nullable();
            $table->decimal('total_debits', 15, 2);
            $table->decimal('total_credits', 15, 2);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'posted'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['branch_id', 'transaction_date']);
            $table->index(['status', 'transaction_date']);
            $table->index('journal_number');
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->text('description');
            $table->timestamps();
            
            $table->index(['journal_entry_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};
