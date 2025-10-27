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
        Schema::create('collaterals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // property, vehicle, equipment, jewelry, etc.
            $table->text('description');
            $table->decimal('value', 15, 2);
            $table->string('location')->nullable();
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->string('ownership_document')->nullable();
            $table->date('valuation_date')->nullable();
            $table->string('valued_by')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'released'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('documents')->nullable(); // Store file paths as JSON array
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('client_id');
            $table->index('loan_id');
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collaterals');
    }
};
