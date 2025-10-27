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
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'loan_term')) {
                $table->integer('loan_term')->nullable()->after('term_months');
            }
            if (!Schema::hasColumn('loans', 'monthly_payment')) {
                $table->decimal('monthly_payment', 15, 2)->nullable()->after('interest_rate');
            }
            if (!Schema::hasColumn('loans', 'total_interest')) {
                $table->decimal('total_interest', 15, 2)->nullable()->after('monthly_payment');
            }
            if (!Schema::hasColumn('loans', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->nullable()->after('total_interest');
            }
            if (!Schema::hasColumn('loans', 'repayment_schedule')) {
                $table->json('repayment_schedule')->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('loans', 'next_payment_amount')) {
                $table->decimal('next_payment_amount', 15, 2)->nullable()->after('next_due_date');
            }
            if (!Schema::hasColumn('loans', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }
            if (!Schema::hasColumn('loans', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'loan_term',
                'monthly_payment',
                'total_interest',
                'total_amount',
                'repayment_schedule',
                'next_payment_amount',
                'reviewed_by',
                'reviewed_at'
            ]);
        });
    }
};

