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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type'); // sms, email, push, in_app
            $table->string('category'); // payment_reminder, overdue_alert, approval_notification, etc.
            $table->string('title');
            $table->text('message');
            
            // Recipients
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            
            // Related Entities
            $table->string('related_type')->nullable(); // loan, savings_account, transaction, etc.
            $table->unsignedBigInteger('related_id')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            
            // Delivery Status
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'bounced'])->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('failure_reason')->nullable();
            
            // Retry Logic
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->timestamp('next_retry_at')->nullable();
            
            // Template and Content
            $table->string('template_id')->nullable();
            $table->json('template_variables')->nullable();
            $table->json('delivery_options')->nullable(); // SMS provider, email settings, etc.
            
            // Priority and Urgency
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('requires_read_confirmation')->default(false);
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['status', 'scheduled_at']);
            $table->index(['notification_type', 'category']);
            $table->index(['related_type', 'related_id']);
            $table->index(['branch_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};