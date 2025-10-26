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
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('chart_of_accounts', 'current_balance')) {
                $table->decimal('current_balance', 15, 2)->default(0)->after('opening_balance');
            }
            if (!Schema::hasColumn('chart_of_accounts', 'last_transaction_date')) {
                $table->date('last_transaction_date')->nullable()->after('current_balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropColumn(['current_balance', 'last_transaction_date']);
        });
    }
};

