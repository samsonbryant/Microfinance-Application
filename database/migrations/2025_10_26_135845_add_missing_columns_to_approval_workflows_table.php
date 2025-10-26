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
        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->foreignId('loan_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('level')->default(1);
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->dropForeign(['loan_id']);
            $table->dropForeign(['approver_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['loan_id', 'level', 'approver_id', 'status', 'comments', 'created_by', 'reviewed_by', 'reviewed_at']);
        });
    }
};
