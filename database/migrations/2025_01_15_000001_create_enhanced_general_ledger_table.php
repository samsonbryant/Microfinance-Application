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
        Schema::create('general_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->date('transaction_date');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->text('description');
            $table->string('reference_number')->nullable();
            $table->string('reference_type')->nullable(); // loan, savings, expense, journal, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('voucher_number')->nullable();
            $table->json('metadata')->nullable(); // Additional transaction data
            $table->timestamps();
            
            $table->index(['account_id', 'transaction_date']);
            $table->index(['branch_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['status', 'transaction_date']);
            $table->index('entry_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledger_entries');
    }
};
