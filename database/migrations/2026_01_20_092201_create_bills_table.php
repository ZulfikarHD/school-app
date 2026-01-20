<?php

/**
 * Migration untuk tabel bills (tagihan)
 *
 * Tabel ini menyimpan tagihan yang di-generate untuk setiap siswa
 * berdasarkan kategori pembayaran dengan tracking status pembayaran
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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tagihan', 50)->unique();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_category_id')->constrained()->cascadeOnDelete();
            $table->string('tahun_ajaran', 20);
            $table->tinyInteger('bulan')->nullable()->comment('1-12 untuk tagihan bulanan');
            $table->year('tahun');
            $table->decimal('nominal', 15, 2);
            $table->decimal('nominal_terbayar', 15, 2)->default(0);
            $table->decimal('nominal_sisa', 15, 2)->virtualAs('nominal - nominal_terbayar');
            $table->enum('status', ['belum_bayar', 'sebagian', 'lunas', 'dibatalkan'])->default('belum_bayar');
            $table->date('tanggal_jatuh_tempo');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'status']);
            $table->index(['tahun_ajaran', 'bulan', 'tahun']);
            $table->index(['status', 'tanggal_jatuh_tempo']);
            $table->index('payment_category_id');

            // Prevent duplicate bills
            $table->unique(['student_id', 'payment_category_id', 'tahun_ajaran', 'bulan', 'tahun'], 'unique_student_bill');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
