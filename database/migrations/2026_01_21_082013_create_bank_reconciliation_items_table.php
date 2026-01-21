<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel bank_reconciliation_items
 *
 * Menyimpan data detail setiap transaksi dari statement bank
 * dan hasil matching dengan pembayaran di sistem
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')
                ->constrained('bank_reconciliations')
                ->cascadeOnDelete();
            $table->date('transaction_date')
                ->comment('Tanggal transaksi dari statement');
            $table->string('description')
                ->nullable()
                ->comment('Deskripsi/berita transaksi');
            $table->decimal('amount', 15, 2)
                ->comment('Nominal transaksi');
            $table->enum('transaction_type', ['credit', 'debit'])
                ->default('credit')
                ->comment('Tipe transaksi (credit = masuk, debit = keluar)');
            $table->string('reference')
                ->nullable()
                ->comment('Nomor referensi transaksi');
            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete()
                ->comment('ID pembayaran yang di-match');
            $table->enum('match_type', ['auto', 'manual', 'unmatched'])
                ->default('unmatched')
                ->comment('Tipe matching');
            $table->decimal('match_confidence', 5, 2)
                ->nullable()
                ->comment('Confidence score untuk auto-match (0-100)');
            $table->timestamp('matched_at')
                ->nullable();
            $table->foreignId('matched_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('notes')
                ->nullable()
                ->comment('Catatan untuk item ini');
            $table->timestamps();

            // Index untuk query yang sering digunakan
            $table->index(['reconciliation_id', 'match_type']);
            $table->index(['payment_id']);
            $table->index(['transaction_date', 'amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_items');
    }
};
