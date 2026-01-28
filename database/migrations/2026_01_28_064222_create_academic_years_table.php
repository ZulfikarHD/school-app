<?php

/**
 * Migration untuk tabel academic_years yang menyimpan data tahun ajaran
 * untuk referensi sistem PSB dan akademik lainnya
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk membuat tabel academic_years
     * dengan field nama, tanggal mulai/selesai, dan status aktif
     */
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique()->comment('Format: 2025/2026');
            $table->date('start_date')->comment('Tanggal mulai tahun ajaran');
            $table->date('end_date')->comment('Tanggal selesai tahun ajaran');
            $table->boolean('is_active')->default(false)->comment('Status tahun ajaran aktif');
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
