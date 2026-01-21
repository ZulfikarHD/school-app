<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel payment_transactions
 *
 * Tabel ini menyimpan data transaksi pembayaran yang mencakup
 * satu atau lebih tagihan (bills) dalam satu transaksi pembayaran.
 * Refactor dari model 1:1 Payment-Bill menjadi 1:N Transaction-Bills.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // Transaction identifier
            $table->string('transaction_number', 50)->unique()
                ->comment('Nomor transaksi unik (format: TRX/YYYY/MM/#####)');

            // Guardian relationship untuk tracking siapa yang submit
            $table->foreignId('guardian_id')->nullable()
                ->constrained('guardians')
                ->nullOnDelete()
                ->comment('FK ke guardians - untuk parent submission');

            // Financial data
            $table->decimal('total_amount', 15, 2)
                ->comment('Total nominal transaksi (sum dari payment_items)');

            // Payment details
            $table->enum('payment_method', ['tunai', 'transfer', 'qris'])
                ->comment('Metode pembayaran: tunai, transfer bank, atau QRIS');
            $table->date('payment_date')
                ->comment('Tanggal pembayaran dilakukan');
            $table->time('payment_time')->nullable()
                ->comment('Waktu pembayaran (opsional)');
            $table->string('proof_file', 255)->nullable()
                ->comment('Path file bukti transfer/pembayaran');
            $table->text('notes')->nullable()
                ->comment('Catatan atau keterangan tambahan');

            // Status tracking
            $table->enum('status', ['pending', 'verified', 'cancelled'])->default('pending')
                ->comment('Status transaksi: pending, verified, atau cancelled');

            // Verification tracking
            $table->foreignId('verified_by')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('FK ke users - siapa yang memverifikasi');
            $table->timestamp('verified_at')->nullable()
                ->comment('Waktu verifikasi');

            // Cancellation tracking
            $table->foreignId('cancelled_by')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('FK ke users - siapa yang membatalkan');
            $table->timestamp('cancelled_at')->nullable()
                ->comment('Waktu pembatalan');
            $table->text('cancellation_reason')->nullable()
                ->comment('Alasan pembatalan');

            // Audit fields
            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('FK ke users - siapa yang membuat');
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('FK ke users - siapa yang terakhir update');

            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk query optimization
            $table->index('status');
            $table->index('payment_date');
            $table->index('payment_method');
            $table->index(['status', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
