<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel payment_items
 *
 * Tabel ini menyimpan detail pembayaran per tagihan (bill) dalam
 * satu transaksi pembayaran (payment_transaction).
 * Mendukung model 1:N dimana satu transaksi bisa membayar multiple bills.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();

            // Parent transaction relationship
            $table->foreignId('payment_transaction_id')
                ->constrained('payment_transactions')
                ->cascadeOnDelete()
                ->comment('FK ke payment_transactions - parent transaction');

            // Bill relationship
            $table->foreignId('bill_id')
                ->constrained('bills')
                ->restrictOnDelete()
                ->comment('FK ke bills - tagihan yang dibayar');

            // Student relationship untuk multi-child support
            $table->foreignId('student_id')
                ->constrained('students')
                ->restrictOnDelete()
                ->comment('FK ke students - siswa pemilik tagihan');

            // Payment amount untuk bill ini
            $table->decimal('amount', 15, 2)
                ->comment('Nominal yang dibayar untuk tagihan ini');

            $table->timestamps();

            // Unique constraint: satu bill hanya bisa ada sekali per transaction
            $table->unique(['payment_transaction_id', 'bill_id'], 'unique_transaction_bill');

            // Indexes untuk query optimization
            $table->index('bill_id');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_items');
    }
};
