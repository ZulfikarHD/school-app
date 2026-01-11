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
        Schema::create('attendance_notifications_queue', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['alpha_alert', 'reminder', 'weekly_summary', 'monthly_report']);
            $table->foreignId('recipient_user_id')->constrained('users')->onDelete('cascade');
            $table->string('recipient_phone', 15)->nullable();
            $table->string('recipient_email', 100)->nullable();

            // Content
            $table->string('subject')->nullable();
            $table->text('message');

            // Status
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->enum('delivery_method', ['whatsapp', 'email', 'in_app']);
            $table->timestamp('sent_at')->nullable();
            $table->text('failed_reason')->nullable();
            $table->integer('retry_count')->default(0);

            // Reference
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('created_at');
            $table->index('recipient_user_id');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_notifications_queue');
    }
};
