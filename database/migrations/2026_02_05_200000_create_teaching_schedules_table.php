<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk membuat tabel teaching_schedules
 *
 * Tabel ini bertujuan untuk menyimpan jadwal mengajar guru per kelas,
 * mencakup informasi hari, waktu, ruangan, dan tahun ajaran
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teaching_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('ruangan', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Composite indexes untuk optimasi query conflict detection
            // Index untuk mencari jadwal guru di hari dan waktu tertentu
            $table->index(
                ['teacher_id', 'hari', 'jam_mulai', 'jam_selesai', 'academic_year_id'],
                'idx_teacher_schedule'
            );

            // Index untuk mencari jadwal kelas di hari dan waktu tertentu
            $table->index(
                ['class_id', 'hari', 'jam_mulai', 'jam_selesai', 'academic_year_id'],
                'idx_class_schedule'
            );

            // Index untuk filter berdasarkan tahun ajaran dan status aktif
            $table->index(['academic_year_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_schedules');
    }
};
