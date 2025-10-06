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
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Communication Details
            $table->enum('communication_type', ['phone_call', 'sms', 'email', 'visit', 'letter', 'whatsapp', 'other'])->default('phone_call');
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->enum('purpose', [
                'payment_reminder',
                'overdue_notification',
                'loan_application_follow_up',
                'kyc_verification',
                'general_inquiry',
                'complaint',
                'feedback',
                'marketing',
                'other'
            ])->default('payment_reminder');
            
            // Contact Information
            $table->string('contact_number')->nullable();
            $table->string('email_address')->nullable();
            $table->text('contact_address')->nullable();
            
            // Communication Content
            $table->text('subject')->nullable();
            $table->longText('message_content');
            $table->text('client_response')->nullable();
            $table->text('notes')->nullable();
            
            // Status and Outcome
            $table->enum('status', ['sent', 'delivered', 'read', 'responded', 'failed', 'bounced'])->default('sent');
            $table->enum('outcome', [
                'successful',
                'no_response',
                'client_unavailable',
                'wrong_number',
                'refused_communication',
                'promised_payment',
                'requested_callback',
                'escalated',
                'resolved'
            ])->nullable();
            
            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            
            // Duration (for calls/visits)
            $table->integer('duration_minutes')->nullable();
            $table->text('call_summary')->nullable();
            
            // Follow-up
            $table->boolean('requires_follow_up')->default(false);
            $table->timestamp('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->foreignId('follow_up_assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Attachments
            $table->json('attachments')->nullable(); // File paths
            $table->json('metadata')->nullable(); // Additional data like SMS delivery status, etc.
            
            // External References
            $table->string('external_id')->nullable(); // ID from SMS/Email service
            $table->string('external_status')->nullable();
            $table->text('external_error')->nullable();
            
            // Cost Tracking
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('cost_currency', 3)->default('USD');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['client_id', 'communication_type']);
            $table->index(['loan_id', 'purpose']);
            $table->index(['user_id', 'created_at']);
            $table->index(['branch_id', 'created_at']);
            $table->index(['status', 'outcome']);
            $table->index(['scheduled_at', 'status']);
            $table->index(['follow_up_date', 'requires_follow_up']);
            $table->index(['external_id', 'communication_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};