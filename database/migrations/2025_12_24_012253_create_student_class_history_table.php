<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel student_class_history untuk tracking riwayat perpindahan kelas siswa
     * yang digunakan untuk audit trail dan laporan historis kelas siswa
     */
    public function up(): void
    {
        Schema::create('student_class_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedBigInteger('kelas_id')->comment('FK to classes table (future)');

            $table->string('tahun_ajaran', 9)->comment('Format: 2024/2025');
            $table->string('wali_kelas', 100)->nullable()->comment('Nama wali kelas');

            $table->timestamps();

            // Indexes
            $table->index('student_id');
            $table->index('kelas_id');
            $table->index('tahun_ajaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_history');
    }
};
