<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            // Add alias columns for compatibility
            if (!Schema::hasColumn('loan_applications', 'term_months')) {
                $table->integer('term_months')->nullable()->after('requested_amount');
            }
            if (!Schema::hasColumn('loan_applications', 'interest_rate')) {
                $table->decimal('interest_rate', 5, 2)->nullable()->after('term_months');
            }
            if (!Schema::hasColumn('loan_applications', 'purpose')) {
                $table->text('purpose')->nullable()->after('interest_rate');
            }
            if (!Schema::hasColumn('loan_applications', 'employment_status')) {
                $table->string('employment_status')->nullable()->after('monthly_expenses');
            }
            if (!Schema::hasColumn('loan_applications', 'collateral_type')) {
                $table->string('collateral_type')->nullable()->after('employment_status');
            }
            if (!Schema::hasColumn('loan_applications', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('collateral_value')->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn(['term_months', 'interest_rate', 'purpose', 'employment_status', 'collateral_type', 'created_by']);
        });
    }
};
