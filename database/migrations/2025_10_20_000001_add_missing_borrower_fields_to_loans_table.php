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
            // Add loan_purpose column if it doesn't exist
            if (!Schema::hasColumn('loans', 'loan_purpose')) {
                $table->text('loan_purpose')->nullable()->after('loan_type');
            }
            
            // Add application_date if it doesn't exist
            if (!Schema::hasColumn('loans', 'application_date')) {
                $table->date('application_date')->nullable()->after('disbursement_date');
            }
            
            // Add approved_by if it doesn't exist
            if (!Schema::hasColumn('loans', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }
            
            // Add approved_at if it doesn't exist
            if (!Schema::hasColumn('loans', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            
            // Add rejected_by if it doesn't exist
            if (!Schema::hasColumn('loans', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
            }
            
            // Add rejected_at if it doesn't exist
            if (!Schema::hasColumn('loans', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            
            // Add next_due_date if it doesn't exist
            if (!Schema::hasColumn('loans', 'next_due_date')) {
                $table->date('next_due_date')->nullable()->after('due_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $columns = [
                'loan_purpose', 
                'application_date', 
                'approved_by', 
                'approved_at', 
                'rejected_by', 
                'rejected_at',
                'next_due_date'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('loans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

