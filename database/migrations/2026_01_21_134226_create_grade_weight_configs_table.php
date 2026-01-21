<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration untuk tabel grade_weight_configs yang menyimpan konfigurasi
     * bobot komponen nilai (UH, UTS, UAS, Praktik) untuk perhitungan nilai akhir
     * sesuai format Kurikulum 2013
     */
    public function up(): void
    {
        Schema::create('grade_weight_configs', function (Blueprint $table) {
            $table->id();

            // Konteks konfigurasi
            $table->string('tahun_ajaran', 9)->comment('Format: 2024/2025');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade')
                ->comment('Null = konfigurasi default untuk semua mapel');

            // Bobot komponen nilai dalam persen (total harus 100%)
            $table->unsignedTinyInteger('uh_weight')->default(30)
                ->comment('Bobot Ulangan Harian dalam persen');
            $table->unsignedTinyInteger('uts_weight')->default(25)
                ->comment('Bobot UTS dalam persen');
            $table->unsignedTinyInteger('uas_weight')->default(30)
                ->comment('Bobot UAS dalam persen');
            $table->unsignedTinyInteger('praktik_weight')->default(15)
                ->comment('Bobot Praktik/Proyek dalam persen');

            // Flag untuk konfigurasi default
            $table->boolean('is_default')->default(false)
                ->comment('True jika ini adalah konfigurasi default untuk tahun ajaran');

            $table->timestamps();

            // Unique constraint - 1 konfigurasi per subject per tahun ajaran
            // atau 1 default per tahun ajaran
            $table->unique(['tahun_ajaran', 'subject_id'], 'grade_weight_configs_unique');

            // Index untuk quick lookup default config
            $table->index(['tahun_ajaran', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_weight_configs');
    }
};
