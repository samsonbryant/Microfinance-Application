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
        Schema::create('revenue_entries', function (Blueprint $table) {
            $table->id();
            $table->string('revenue_number')->unique();
            $table->date('transaction_date');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->cascadeOnDelete(); // Revenue account
            $table->enum('revenue_type', ['interest_received', 'default_charges', 'processing_fee', 'system_charge', 'other'])->default('other');
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->foreignId('loan_id')->nullable()->constrained('loans')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
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
            $table->index('revenue_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_entries');
    }
};

