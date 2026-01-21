<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration untuk tabel attitude_grades yang menyimpan nilai sikap siswa
     * mencakup aspek spiritual dan sosial sesuai format Kurikulum 2013
     * yang diinput oleh wali kelas
     */
    public function up(): void
    {
        Schema::create('attitude_grades', function (Blueprint $table) {
            $table->id();

            // Foreign keys untuk relasi
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade')
                ->comment('Wali kelas yang menginput nilai sikap');

            // Konteks akademik
            $table->string('tahun_ajaran', 9)->comment('Format: 2024/2025');
            $table->enum('semester', ['1', '2'])->comment('Semester 1 atau 2');

            // Nilai sikap spiritual (KI-1)
            $table->enum('spiritual_grade', ['A', 'B', 'C', 'D'])
                ->comment('Predikat nilai spiritual: A=Sangat Baik, B=Baik, C=Cukup, D=Kurang');
            $table->string('spiritual_description', 200)->nullable()
                ->comment('Deskripsi sikap spiritual siswa');

            // Nilai sikap sosial (KI-2)
            $table->enum('social_grade', ['A', 'B', 'C', 'D'])
                ->comment('Predikat nilai sosial: A=Sangat Baik, B=Baik, C=Cukup, D=Kurang');
            $table->string('social_description', 200)->nullable()
                ->comment('Deskripsi sikap sosial siswa');

            // Catatan wali kelas untuk rapor
            $table->text('homeroom_notes')->nullable()
                ->comment('Catatan umum wali kelas untuk rapor (max 500 karakter)');

            $table->timestamps();

            // Unique constraint untuk memastikan 1 siswa hanya punya 1 nilai sikap per semester
            $table->unique(['student_id', 'tahun_ajaran', 'semester'], 'attitude_grades_unique');

            // Indexes untuk optimasi query
            $table->index(['class_id', 'tahun_ajaran', 'semester']);
            $table->index(['teacher_id', 'tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attitude_grades');
    }
};
