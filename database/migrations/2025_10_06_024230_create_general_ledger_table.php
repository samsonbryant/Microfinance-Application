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
        Schema::create('general_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();
            $table->date('entry_date');
            $table->string('reference_type'); // loan, savings, transaction, adjustment, etc.
            $table->unsignedBigInteger('reference_id'); // ID of the related record
            $table->text('description');
            
            // Double Entry
            $table->string('debit_account_code');
            $table->string('credit_account_code');
            $table->decimal('debit_amount', 15, 2);
            $table->decimal('credit_amount', 15, 2);
            $table->decimal('balance', 15, 2); // Running balance
            
            // Branch and User Information
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Status and Control
            $table->enum('status', ['pending', 'approved', 'reversed', 'cancelled'])->default('pending');
            $table->string('reversal_reference')->nullable(); // Reference to reversed entry
            $table->text('notes')->nullable();
            
            // Audit Trail
            $table->string('created_ip')->nullable();
            $table->string('created_user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['entry_date', 'branch_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['debit_account_code', 'credit_account_code']);
            $table->index(['status', 'entry_date']);
            $table->index(['branch_id', 'created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledger');
    }
};