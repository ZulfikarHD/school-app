<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration untuk tabel grades yang menyimpan nilai siswa
     * untuk berbagai jenis penilaian (UH, UTS, UAS, Praktik) sesuai K13
     */
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();

            // Foreign keys untuk relasi ke tabel terkait
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');

            // Konteks akademik
            $table->string('tahun_ajaran', 9)->comment('Format: 2024/2025');
            $table->enum('semester', ['1', '2'])->comment('Semester 1 atau 2');

            // Detail penilaian
            $table->enum('assessment_type', ['UH', 'UTS', 'UAS', 'PRAKTIK', 'PROYEK'])
                ->comment('Jenis penilaian: UH=Ulangan Harian, UTS=Ujian Tengah Semester, UAS=Ujian Akhir Semester');
            $table->unsignedTinyInteger('assessment_number')->nullable()
                ->comment('Nomor urut penilaian untuk UH (1, 2, 3, dst)');
            $table->string('title', 100)->comment('Judul penilaian, contoh: UH 1: Perkalian');
            $table->date('assessment_date')->comment('Tanggal pelaksanaan penilaian');

            // Nilai
            $table->decimal('score', 5, 2)->comment('Nilai 0-100 dengan 2 desimal');
            $table->text('notes')->nullable()->comment('Catatan tambahan untuk nilai');

            // Lock mechanism untuk finalisasi nilai
            $table->boolean('is_locked')->default(false)->comment('Flag apakah nilai sudah dikunci');
            $table->timestamp('locked_at')->nullable()->comment('Waktu nilai dikunci');
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null')
                ->comment('User yang mengunci nilai');

            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk optimasi query
            $table->index(['student_id', 'subject_id', 'tahun_ajaran', 'semester'], 'grades_student_subject_period_idx');
            $table->index(['class_id', 'subject_id', 'tahun_ajaran', 'semester'], 'grades_class_subject_period_idx');
            $table->index(['teacher_id', 'tahun_ajaran', 'semester']);
            $table->index(['assessment_type', 'tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
