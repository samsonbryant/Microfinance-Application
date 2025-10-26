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
            // Only add columns that don't exist
            if (!Schema::hasColumn('loans', 'monthly_payment')) {
                $table->decimal('monthly_payment', 15, 2)->nullable()->after('term_months');
            }
            if (!Schema::hasColumn('loans', 'next_payment_date')) {
                $table->date('next_payment_date')->nullable()->after('disbursement_date');
            }
            if (!Schema::hasColumn('loans', 'outstanding_balance')) {
                $table->decimal('outstanding_balance', 15, 2)->default(0)->after('next_payment_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['monthly_payment', 'disbursement_date', 'next_payment_date', 'outstanding_balance']);
        });
    }
};
