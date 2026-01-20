<?php

/**
 * Migration untuk tabel payment_categories
 *
 * Tabel ini menyimpan kategori pembayaran sekolah seperti SPP, Uang Gedung,
 * Seragam, Kegiatan, dan Donasi dengan konfigurasi harga per kelas
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
        Schema::create('payment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kode', 20)->unique();
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['bulanan', 'tahunan', 'insidental'])->default('bulanan');
            $table->decimal('nominal_default', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_mandatory')->default(true);
            $table->integer('due_day')->nullable()->comment('Tanggal jatuh tempo setiap bulan (1-28)');
            $table->string('tahun_ajaran', 20)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'tipe']);
            $table->index('tahun_ajaran');
        });

        // Tabel untuk konfigurasi harga per kelas (optional price per class)
        Schema::create('payment_category_class_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->decimal('nominal', 15, 2);
            $table->timestamps();

            $table->unique(['payment_category_id', 'class_id'], 'category_class_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_category_class_prices');
        Schema::dropIfExists('payment_categories');
    }
};
