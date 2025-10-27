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
        // Skip - kyc_documents table already created in 2025_10_06_003034
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip
    }
};
