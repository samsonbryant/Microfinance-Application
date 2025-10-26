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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Cash on Hand, Orange Money, BnB, GT Bank, etc.
            $table->enum('type', ['cash', 'bank', 'mobile_money'])->default('bank');
            $table->foreignId('account_id')->nullable()->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->string('account_number')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('branch_name')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};

