<?php

/**
 * Migration untuk tabel psb_payments yang menyimpan data pembayaran
 * biaya pendaftaran dan daftar ulang PSB
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk membuat tabel psb_payments
     * dengan informasi pembayaran dan status verifikasi
     */
    public function up(): void
    {
        Schema::create('psb_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psb_registration_id')
                ->constrained('psb_registrations')
                ->onDelete('cascade')
                ->comment('Relasi ke data pendaftaran');
            $table->enum('payment_type', [
                'registration_fee',    // Biaya pendaftaran
                're_registration_fee', // Biaya daftar ulang
            ])->comment('Jenis pembayaran');
            $table->unsignedInteger('amount')->comment('Jumlah pembayaran dalam Rupiah');
            $table->enum('payment_method', [
                'transfer',   // Transfer bank
                'cash',       // Tunai
                'qris',       // QRIS
            ])->comment('Metode pembayaran');
            $table->string('proof_file_path', 255)->nullable()->comment('Path bukti pembayaran');
            $table->enum('status', [
                'pending',    // Menunggu verifikasi
                'verified',   // Sudah diverifikasi
                'rejected',   // Ditolak
            ])->default('pending')->comment('Status verifikasi pembayaran');
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User yang memverifikasi');
            $table->timestamp('verified_at')->nullable()->comment('Waktu verifikasi');
            $table->text('notes')->nullable()->comment('Catatan pembayaran');
            $table->timestamps();

            $table->index(['psb_registration_id', 'payment_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('psb_payments');
    }
};
