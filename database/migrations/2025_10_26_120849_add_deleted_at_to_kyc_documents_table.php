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
        // Skip - deleted_at already exists (soft deletes in base migration)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip
    }
};
