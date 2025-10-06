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
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_name');
            $table->enum('report_type', [
                'balance_sheet', 
                'profit_loss', 
                'cash_flow', 
                'trial_balance', 
                'portfolio_at_risk',
                'loan_performance',
                'branch_performance',
                'client_demographics',
                'monthly_summary',
                'yearly_summary'
            ]);
            $table->date('report_date');
            $table->date('period_start');
            $table->date('period_end');
            
            // Report Data
            $table->json('report_data'); // Store the actual report data
            $table->json('parameters')->nullable(); // Report generation parameters
            
            // Branch and User Information
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            
            // Report Status
            $table->enum('status', ['generating', 'completed', 'failed'])->default('generating');
            $table->text('error_message')->nullable();
            $table->timestamp('generated_at')->nullable();
            
            // File Information
            $table->string('file_path')->nullable();
            $table->string('file_format')->nullable(); // pdf, excel, csv
            $table->integer('file_size')->nullable();
            
            // Access Control
            $table->boolean('is_public')->default(false);
            $table->json('access_permissions')->nullable(); // Who can access this report
            
            $table->timestamps();
            
            // Indexes
            $table->index(['report_type', 'report_date']);
            $table->index(['branch_id', 'report_type']);
            $table->index(['generated_by', 'created_at']);
            $table->index(['status', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};