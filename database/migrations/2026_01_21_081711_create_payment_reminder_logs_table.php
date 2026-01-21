<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel payment_reminder_logs
 *
 * Menyimpan log pengiriman reminder pembayaran ke orang tua siswa
 * untuk tracking H-5, H-0 (jatuh tempo), dan H+7 (overdue) reminders
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')
                ->constrained('bills')
                ->cascadeOnDelete();
            $table->enum('reminder_type', ['h_minus_5', 'due_date', 'h_plus_7'])
                ->comment('Tipe reminder: H-5, Jatuh tempo, H+7');
            $table->enum('channel', ['whatsapp', 'email'])
                ->comment('Channel pengiriman reminder');
            $table->string('recipient')
                ->comment('Nomor HP atau email penerima');
            $table->text('message')
                ->comment('Isi pesan yang dikirim');
            $table->enum('status', ['pending', 'sent', 'failed'])
                ->default('pending')
                ->comment('Status pengiriman');
            $table->timestamp('sent_at')
                ->nullable()
                ->comment('Waktu berhasil terkirim');
            $table->text('error_message')
                ->nullable()
                ->comment('Pesan error jika gagal');
            $table->timestamps();

            // Index untuk query yang sering digunakan
            $table->index(['bill_id', 'reminder_type']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reminder_logs');
    }
};
