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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->date('transaction_date');
            $table->foreignId('from_account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->foreignId('to_account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->foreignId('from_bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->foreignId('to_bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['deposit', 'withdrawal', 'disbursement', 'expense', 'transfer'])->default('transfer');
            $table->string('reference_number')->nullable();
            $table->text('description');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'posted'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('transaction_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};

