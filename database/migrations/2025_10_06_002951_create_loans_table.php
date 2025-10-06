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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('collateral_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('loan_type', ['personal', 'business', 'agricultural', 'education', 'housing', 'micro', 'group', 'emergency'])->default('personal');
            $table->decimal('amount', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->integer('term_months');
            $table->enum('payment_frequency', ['weekly', 'biweekly', 'monthly', 'quarterly', 'lump_sum'])->default('monthly');
            $table->date('disbursement_date')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'disbursed', 'active', 'overdue', 'completed', 'cancelled', 'defaulted'])->default('pending');
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->decimal('penalty_rate', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
