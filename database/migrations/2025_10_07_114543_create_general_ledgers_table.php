<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->string('transaction_number')->unique();
            $table->date('transaction_date');
            $table->string('description');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('reference_type')->nullable(); // e.g., 'loan', 'transaction', 'payment'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'transaction_date']);
            $table->index('transaction_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_ledgers');
    }
};
