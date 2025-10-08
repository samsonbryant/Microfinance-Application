<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Personal Information
            if (!Schema::hasColumn('clients', 'avatar')) {
                $table->string('avatar')->nullable()->after('client_number');
            }
            if (!Schema::hasColumn('clients', 'marital_status')) {
                $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            }
            
            // Identification
            if (!Schema::hasColumn('clients', 'identification_type')) {
                $table->string('identification_type')->nullable()->after('marital_status');
            }
            if (!Schema::hasColumn('clients', 'identification_number')) {
                $table->string('identification_number')->nullable()->after('identification_type');
            }
            
            // Employment Details
            if (!Schema::hasColumn('clients', 'employer')) {
                $table->string('employer')->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('clients', 'employee_number')) {
                $table->string('employee_number')->nullable()->after('employer');
            }
            if (!Schema::hasColumn('clients', 'tax_number')) {
                $table->string('tax_number')->nullable()->after('employee_number');
            }
            
            // Contact Information
            if (!Schema::hasColumn('clients', 'primary_phone_country')) {
                $table->string('primary_phone_country')->default('US')->after('phone');
            }
            if (!Schema::hasColumn('clients', 'secondary_phone')) {
                $table->string('secondary_phone')->nullable()->after('primary_phone_country');
            }
            if (!Schema::hasColumn('clients', 'secondary_phone_country')) {
                $table->string('secondary_phone_country')->nullable()->after('secondary_phone');
            }
            
            // Address Details
            if (!Schema::hasColumn('clients', 'zip_code')) {
                $table->string('zip_code')->nullable()->after('state');
            }
            
            // Files
            if (!Schema::hasColumn('clients', 'files')) {
                $table->json('files')->nullable()->after('zip_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'marital_status', 'identification_type', 'identification_number',
                'employer', 'employee_number', 'tax_number',
                'primary_phone_country', 'secondary_phone', 'secondary_phone_country',
                'zip_code', 'files'
            ]);
        });
    }
};
