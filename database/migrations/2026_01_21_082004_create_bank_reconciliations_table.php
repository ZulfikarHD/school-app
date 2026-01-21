<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel bank_reconciliations
 *
 * Menyimpan data header rekonsiliasi bank, termasuk informasi file
 * statement yang diupload dan summary hasil matching
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->string('filename')
                ->comment('Nama file statement yang diupload');
            $table->string('original_filename')
                ->comment('Nama file asli');
            $table->string('bank_name')
                ->nullable()
                ->comment('Nama bank (BCA, Mandiri, dll)');
            $table->date('statement_date')
                ->nullable()
                ->comment('Tanggal statement');
            $table->date('statement_start_date')
                ->nullable()
                ->comment('Tanggal awal periode statement');
            $table->date('statement_end_date')
                ->nullable()
                ->comment('Tanggal akhir periode statement');
            $table->integer('total_transactions')
                ->default(0)
                ->comment('Total transaksi dalam statement');
            $table->decimal('total_amount', 15, 2)
                ->default(0)
                ->comment('Total nominal semua transaksi');
            $table->integer('matched_count')
                ->default(0)
                ->comment('Jumlah transaksi yang berhasil di-match');
            $table->decimal('matched_amount', 15, 2)
                ->default(0)
                ->comment('Total nominal yang berhasil di-match');
            $table->integer('unmatched_count')
                ->default(0)
                ->comment('Jumlah transaksi yang tidak ter-match');
            $table->enum('status', ['draft', 'processing', 'completed', 'verified'])
                ->default('draft')
                ->comment('Status rekonsiliasi');
            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('verified_at')
                ->nullable();
            $table->text('notes')
                ->nullable()
                ->comment('Catatan tambahan');
            $table->timestamps();

            // Index untuk query yang sering digunakan
            $table->index(['status', 'created_at']);
            $table->index('statement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
    }
};
