<?php

/**
 * Migration untuk tabel payments (pembayaran)
 *
 * Tabel ini menyimpan record pembayaran yang dilakukan terhadap tagihan
 * dengan support untuk partial payment dan multiple payment methods
 */

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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kwitansi', 50)->unique();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('nominal', 15, 2);
            $table->enum('metode_pembayaran', ['tunai', 'transfer', 'qris'])->default('tunai');
            $table->date('tanggal_bayar');
            $table->time('waktu_bayar')->nullable();
            $table->enum('status', ['pending', 'verified', 'cancelled'])->default('verified');
            $table->string('bukti_transfer')->nullable()->comment('Path to uploaded proof image');
            $table->text('keterangan')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'tanggal_bayar']);
            $table->index(['status', 'tanggal_bayar']);
            $table->index('bill_id');
            $table->index('metode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
