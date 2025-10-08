<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->string('fee_name');
            $table->enum('fee_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('fee_amount', 15, 2);
            $table->enum('charge_type', ['upfront', 'on_disbursement', 'on_repayment'])->default('upfront');
            $table->boolean('is_recurring')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('loan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_fees');
    }
};
