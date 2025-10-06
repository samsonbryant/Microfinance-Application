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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // create, update, delete, login, logout, etc.
            $table->string('auditable_type'); // Model class name
            $table->unsignedBigInteger('auditable_id'); // Model ID
            $table->json('old_values')->nullable(); // Previous values
            $table->json('new_values')->nullable(); // New values
            $table->text('event_description')->nullable();
            
            // User Information
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            
            // Request Information
            $table->string('request_url')->nullable();
            $table->string('request_method')->nullable();
            $table->json('request_data')->nullable();
            
            // Branch and Location
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('location')->nullable();
            
            // Security Information
            $table->boolean('is_suspicious')->default(false);
            $table->text('security_notes')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
            
            // Timestamps
            $table->timestamp('event_timestamp')->useCurrent();
            $table->timestamps();
            
            // Indexes
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'event_timestamp']);
            $table->index(['event_type', 'event_timestamp']);
            $table->index(['branch_id', 'event_timestamp']);
            $table->index(['is_suspicious', 'risk_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};