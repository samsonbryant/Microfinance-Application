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
        Schema::create('savings_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('account_type', ['regular', 'fixed_deposit', 'recurring', 'children'])->default('regular');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->decimal('minimum_balance', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'closed', 'suspended'])->default('active');
            $table->date('opening_date')->nullable();
            $table->date('maturity_date')->nullable();
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
        Schema::dropIfExists('savings_accounts');
    }
};
